<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManejaExcepciones;
use App\Models\Asamblea;
use App\Models\MiembroComision;
use App\Models\Socio;
use App\Support\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AsambleaController extends Controller
{
    use ManejaExcepciones;

    public function index(): View
    {
        return $this->ejecutarVista(function () {
            $asambleas = Asamblea::query()
                ->orderByDesc('fecha')
                ->paginate(15);

            return view('asambleas.index', ['asambleas' => $asambleas]);
        }, 'No se pudo cargar el listado de asambleas.');
    }

    public function create(): View
    {
        return $this->ejecutarVista(function () {
            return view('asambleas.create', [
                'socios_habilitados' => Socio::activos()->get()->filter(fn (Socio $s) => $s->tieneDerechoAVoto())->count(),
            ]);
        }, 'No se pudo abrir el formulario de convocatoria.');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tipo' => ['required', 'in:ordinaria,extraordinaria'],
            'motivo' => ['required_if:tipo,extraordinaria', 'nullable', 'string', 'max:255'],
            'fecha_convocatoria' => ['required', 'date'],
            'fecha' => ['required', 'date', 'after:fecha_convocatoria'],
            'hora' => ['nullable'],
            'orden_del_dia' => ['required', 'string'],
        ]);

        $dias = (int) \Carbon\Carbon::parse($validated['fecha_convocatoria'])
            ->diffInDays(\Carbon\Carbon::parse($validated['fecha']));

        // Art. 27° (Ordinaria: 30 días) y Art. 24° (Extraordinaria: entre 5 y 15 días).
        if ($validated['tipo'] === 'ordinaria' && $dias < 30) {
            return back()->withInput()->withErrors([
                'fecha' => "La Asamblea Ordinaria requiere 30 días corridos de anticipación (Art. 27° del Estatuto). Hay solo {$dias} día(s).",
            ]);
        }

        if ($validated['tipo'] === 'extraordinaria' && ($dias < 5 || $dias > 15)) {
            return back()->withInput()->withErrors([
                'fecha' => "La Asamblea Extraordinaria debe convocarse entre 5 y 15 días de anticipación (Art. 24° del Estatuto). Hay {$dias} día(s).",
            ]);
        }

        $validated['socios_activos_habilitados'] = Socio::activos()->get()
            ->filter(fn (Socio $s) => $s->tieneDerechoAVoto())
            ->count();

        return $this->ejecutar(function () use ($validated) {
            $asamblea = Asamblea::create($validated);

            return redirect()->route('asambleas.show', $asamblea)->with('status', 'asamblea-convocada');
        }, 'No se pudo registrar la convocatoria.');
    }

    public function show(Asamblea $asamblea): View
    {
        return $this->ejecutarVista(function () use ($asamblea) {
            $asamblea->load(['asistentes', 'firmantes', 'presidenteAsamblea', 'secretarioActas']);

            return view('asambleas.show', [
                'asamblea' => $asamblea,
                'socios' => Socio::activos()->orderBy('apellido')->get(),
                'firmantesComision' => MiembroComision::vigentes()
                    ->whereIn('cargo', ['presidente', 'secretario'])
                    ->with('socio')
                    ->get(),
            ]);
        }, 'No se pudo cargar la Asamblea.');
    }

    public function asistencia(Request $request, Asamblea $asamblea): RedirectResponse
    {
        $validated = $request->validate([
            'socios_ids' => ['array'],
            'socios_ids.*' => [Tenant::existe('socios')],
        ]);

        return $this->ejecutarEnTransaccion(function () use ($validated, $asamblea) {
            $asamblea->asistentes()->sync($validated['socios_ids'] ?? []);

            return redirect()->route('asambleas.show', $asamblea)->with('status', 'asistencia-registrada');
        }, 'No se pudo guardar la asistencia.');
    }

    /**
     * Art. 36°: se designan 2 socios/as para firmar el Acta junto al/la
     * Presidente/a de la Asamblea y el/la Secretario/a de Actas.
     */
    public function realizar(Request $request, Asamblea $asamblea): RedirectResponse
    {
        $validated = $request->validate([
            'resumen_acta' => ['required', 'string'],
            'presidente_asamblea_id' => ['required', Tenant::existe('socios')],
            'secretario_actas_id' => ['required', Tenant::existe('socios')],
            'firmantes_ids' => ['required', 'array', 'size:2'],
            'firmantes_ids.*' => [Tenant::existe('socios')],
        ]);

        return $this->ejecutarEnTransaccion(function () use ($validated, $asamblea) {
            $asamblea->update([
                'estado' => 'realizada',
                'resumen_acta' => $validated['resumen_acta'],
                'presidente_asamblea_id' => $validated['presidente_asamblea_id'],
                'secretario_actas_id' => $validated['secretario_actas_id'],
            ]);

            $asamblea->firmantes()->sync($validated['firmantes_ids']);

            return redirect()->route('asambleas.show', $asamblea)->with('status', 'asamblea-realizada');
        }, 'No se pudo registrar el acta de la Asamblea.');
    }

    /**
     * Art. 29°: la nulidad de la convocatoria puede plantearse antes o
     * durante la Asamblea.
     */
    public function anular(Request $request, Asamblea $asamblea): RedirectResponse
    {
        $validated = $request->validate([
            'motivo_anulacion' => ['required', 'string', 'max:255'],
        ]);

        return $this->ejecutar(function () use ($validated, $asamblea) {
            $asamblea->update([
                'estado' => 'anulada',
                'motivo_anulacion' => $validated['motivo_anulacion'],
            ]);

            return redirect()->route('asambleas.show', $asamblea)->with('status', 'asamblea-anulada');
        }, 'No se pudo anular la convocatoria.');
    }

    /**
     * Art. 44° del Estatuto: registra que la copia del Acta fue elevada
     * a la Dirección de Cooperación Escolar.
     */
    public function elevar(Request $request, Asamblea $asamblea): RedirectResponse
    {
        $validated = $request->validate([
            'fecha_elevada_direccion' => ['required', 'date'],
        ]);

        return $this->ejecutar(function () use ($validated, $asamblea) {
            $asamblea->update($validated);

            return redirect()->route('asambleas.show', $asamblea)->with('status', 'rendicion-registrada');
        }, 'No se pudo registrar la rendición.');
    }
}
