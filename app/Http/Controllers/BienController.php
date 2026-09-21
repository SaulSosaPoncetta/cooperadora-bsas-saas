<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManejaExcepciones;
use App\Models\Bien;
use App\Models\Establecimiento;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BienController extends Controller
{
    use ManejaExcepciones;

    public function index(Request $request): View
    {
        return $this->ejecutarVista(function () use ($request) {
            $bienes = Bien::query()
                ->when($request->filled('tipo'), fn ($q) => $q->where('tipo', $request->string('tipo')))
                ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->string('estado')))
                ->orderByDesc('fecha_adquisicion')
                ->paginate(20)
                ->withQueryString();

            return view('bienes.index', [
                'bienes' => $bienes,
                'filtros' => $request->only(['tipo', 'estado']),
                'valorTotalEnUso' => Bien::where('estado', '!=', 'dado_de_baja')->sum('valor_estimado'),
            ]);
        }, 'No se pudo cargar el listado de bienes.');
    }

    public function create(): View
    {
        return $this->ejecutarVista(fn () => view('bienes.create'), 'No se pudo abrir el formulario de alta de bien.');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tipo' => ['required', 'in:mueble,inmueble'],
            'descripcion' => ['required', 'string', 'max:255'],
            'fecha_adquisicion' => ['required', 'date'],
            'valor_estimado' => ['nullable', 'numeric', 'min:0'],
            'origen' => ['required', 'in:compra,donacion,fabricado_alumnos,otro'],
        ]);

        return $this->ejecutar(function () use ($validated) {
            Bien::create($validated);

            return redirect()->route('bienes.index')->with('status', 'bien-registrado');
        }, 'No se pudo registrar el bien.');
    }

    /**
     * Art. 16° del Estatuto: cesión de uso a otras entidades, a la
     * escuela o a instituciones de bien público.
     */
    public function ceder(Request $request, Bien $bien): RedirectResponse
    {
        $validated = $request->validate([
            'destino_cesion' => ['required', 'in:otra_cooperadora,escuela,institucion_bien_publico'],
            'cedido_a' => ['required', 'string', 'max:255'],
            'fecha_cesion' => ['required', 'date'],
            'dias_previstos' => ['nullable', 'integer', 'min:0'],
        ]);

        $requiereAprobacion = $validated['destino_cesion'] === 'institucion_bien_publico'
            && ($validated['dias_previstos'] ?? 0) > 30;

        return $this->ejecutar(function () use ($validated, $bien, $requiereAprobacion) {
            $bien->update([
                'estado' => 'cedido',
                'destino_cesion' => $validated['destino_cesion'],
                'cedido_a' => $validated['cedido_a'],
                'fecha_cesion' => $validated['fecha_cesion'],
                'requiere_aprobacion_dgcye' => $requiereAprobacion,
            ]);

            return redirect()->route('bienes.index')->with('status', 'bien-cedido');
        }, 'No se pudo registrar la cesión del bien.');
    }

    public function devolver(Bien $bien): RedirectResponse
    {
        return $this->ejecutar(function () use ($bien) {
            $bien->update([
                'estado' => 'en_uso',
                'destino_cesion' => null,
                'cedido_a' => null,
                'fecha_cesion' => null,
                'requiere_aprobacion_dgcye' => false,
            ]);

            return redirect()->route('bienes.index')->with('status', 'bien-devuelto');
        }, 'No se pudo registrar la devolución del bien.');
    }

    /**
     * Art. 17°: sólo bienes muebles. Venta con 2/3 de la Comisión
     * Directiva en sesión plenaria, o donación con aprobación de la
     * Dirección General de Cultura y Educación.
     */
    public function baja(Request $request, Bien $bien): RedirectResponse
    {
        if (! $bien->puedeDarseDeBaja()) {
            throw ValidationException::withMessages([
                'tipo' => 'Los bienes inmuebles integran el patrimonio fiscal (Art. 18°) y no pueden darse de baja desde este sistema.',
            ]);
        }

        $validated = $request->validate([
            'tipo_baja' => ['required', 'in:venta,donacion'],
            'motivo_baja' => ['required', 'string', 'max:255'],
            'fecha_baja' => ['required', 'date'],
            'aprobacion_baja' => ['accepted'],
        ]);

        return $this->ejecutar(function () use ($validated, $bien) {
            $bien->update([
                'estado' => 'dado_de_baja',
                'tipo_baja' => $validated['tipo_baja'],
                'motivo_baja' => $validated['motivo_baja'],
                'fecha_baja' => $validated['fecha_baja'],
                'aprobacion_baja' => true,
            ]);

            return redirect()->route('bienes.index')->with('status', 'bien-dado-de-baja');
        }, 'No se pudo registrar la baja del bien.');
    }

    /**
     * Reporte de inventario para control físico de los bienes: respeta
     * los mismos filtros de tipo/estado que la pantalla de listado.
     */
    public function inventarioPdf(Request $request): Response
    {
        return $this->ejecutarRespuesta(function () use ($request) {
            $bienes = Bien::query()
                ->when($request->filled('tipo'), fn ($q) => $q->where('tipo', $request->string('tipo')))
                ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->string('estado')))
                ->orderBy('tipo')
                ->orderBy('descripcion')
                ->get();

            $pdf = Pdf::loadView('bienes.pdf.inventario', [
                'establecimiento' => Establecimiento::actual(),
                'bienes' => $bienes,
                'filtros' => $request->only(['tipo', 'estado']),
                'fecha' => now(),
                'valorTotal' => $bienes->where('estado', '!=', 'dado_de_baja')->sum('valor_estimado'),
            ]);

            return $pdf->stream('inventario-bienes-'.now()->format('Y-m-d').'.pdf');
        }, 'No se pudo generar el inventario de bienes.');
    }
}
