<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManejaExcepciones;
use App\Models\MiembroComision;
use App\Models\Socio;
use App\Support\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ComisionController extends Controller
{
    use ManejaExcepciones;
    public function index(): View
    {
        return $this->ejecutarVista(function () {
            $vigentes = MiembroComision::vigentes()
                ->with('socio')
                ->get()
                ->sortBy(fn (MiembroComision $miembro) => array_search($miembro->cargo, array_keys(MiembroComision::CUPOS)) * 10 + ($miembro->orden ?? 0));

            $historial = MiembroComision::whereNotNull('fecha_fin')
                ->with('socio')
                ->orderByDesc('fecha_fin')
                ->paginate(15);

            $cupos = collect(MiembroComision::CUPOS)->map(fn ($total, $cargo) => [
                'total' => $total,
                'disponibles' => MiembroComision::cupoDisponible($cargo),
            ]);

            return view('comision.index', [
                'vigentes' => $vigentes,
                'historial' => $historial,
                'cupos' => $cupos,
            ]);
        }, 'No se pudo cargar la Comisión Directiva.');
    }

    public function create(): View
    {
        return $this->ejecutarVista(function () {
            $socios = Socio::activos()
                ->orderBy('apellido')
                ->get()
                ->filter(fn (Socio $socio) => $socio->tieneDerechoAVoto())
                ->values();

            return view('comision.create', [
                'cupos' => collect(MiembroComision::CUPOS)->map(fn ($total, $cargo) => MiembroComision::cupoDisponible($cargo)),
                'socios' => $socios,
            ]);
        }, 'No se pudo abrir el formulario de asignación de cargo.');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'socio_id' => ['required', Tenant::existe('socios')],
            'cargo' => ['required', Rule::in(array_keys(MiembroComision::CUPOS))],
            'fecha_inicio' => ['required', 'date'],
        ]);

        return $this->ejecutarEnTransaccion(function () use ($validated) {
            $socio = Socio::findOrFail($validated['socio_id']);

            // Art. 26° del Estatuto: sólo los/as socios/as Activos/as con 30
            // días de antigüedad pueden ser elegidos/as miembros de Comisión.
            if (! $socio->tieneDerechoAVoto()) {
                throw ValidationException::withMessages([
                    'socio_id' => 'El/la socio/a debe ser Activo/a y tener al menos 30 días de antigüedad (Art. 26° del Estatuto) para integrar la Comisión Directiva.',
                ]);
            }

            if (MiembroComision::cupoDisponible($validated['cargo']) <= 0) {
                throw ValidationException::withMessages([
                    'cargo' => 'No hay cupo disponible para ese cargo (Art. 3° del Estatuto).',
                ]);
            }

            // ¿Ya ocupa un cargo vigente? No puede acumular dos cargos a la vez.
            if (MiembroComision::vigentes()->where('socio_id', $socio->id)->exists()) {
                throw ValidationException::withMessages([
                    'socio_id' => 'El/la socio/a ya ocupa un cargo vigente en la Comisión Directiva.',
                ]);
            }

            MiembroComision::create([
                'socio_id' => $socio->id,
                'cargo' => $validated['cargo'],
                'orden' => MiembroComision::proximoOrden($validated['cargo']),
                'fecha_inicio' => $validated['fecha_inicio'],
            ]);

            return redirect()->route('comision.index')->with('status', 'miembro-asignado');
        }, 'No se pudo asignar el cargo en la Comisión Directiva.');
    }

    /**
     * Cese de un miembro (renuncia, fin de mandato u otro impedimento —
     * Art. 7° inc. i), 8° inc. g), 9° inc. j) del Estatuto).
     */
    public function cese(Request $request, MiembroComision $miembro): RedirectResponse
    {
        $validated = $request->validate([
            'motivo_cese' => ['required', 'string', 'max:255'],
            'fecha_fin' => ['required', 'date'],
        ]);

        return $this->ejecutar(function () use ($validated, $miembro) {
            $miembro->update($validated);

            return redirect()->route('comision.index')->with('status', 'miembro-cesado');
        }, 'No se pudo registrar el cese.');
    }
}
