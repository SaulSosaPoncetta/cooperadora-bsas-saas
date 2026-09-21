<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManejaExcepciones;
use App\Models\Asamblea;
use App\Models\Establecimiento;
use App\Models\MiembroComision;
use App\Models\MovimientoTesoreria;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReporteController extends Controller
{
    use ManejaExcepciones;

    public function index(): View
    {
        return $this->ejecutarVista(function () {
            return view('reportes.index', [
                'establecimiento' => Establecimiento::actual(),
                'asambleasOrdinarias' => Asamblea::where('tipo', 'ordinaria')
                    ->where('estado', 'realizada')
                    ->orderByDesc('fecha')
                    ->get(),
            ]);
        }, 'No se pudo cargar la sección de Reportes.');
    }

    /**
     * Art. 9° inc. d) del Estatuto: balance con comprobantes, para un
     * período determinado.
     */
    public function balancePdf(Request $request): Response
    {
        $validated = $request->validate([
            'desde' => ['required', 'date'],
            'hasta' => ['required', 'date', 'after_or_equal:desde'],
        ]);

        return $this->ejecutarRespuesta(function () use ($validated) {
            $movimientos = MovimientoTesoreria::query()
                ->entrePeriodo($validated['desde'], $validated['hasta'])
                ->orderBy('fecha')
                ->get();

            $pdf = Pdf::loadView('reportes.pdf.balance', [
                'establecimiento' => Establecimiento::actual(),
                'desde' => $validated['desde'],
                'hasta' => $validated['hasta'],
                'movimientos' => $movimientos,
                'totalIngresos' => $movimientos->where('tipo', 'ingreso')->sum('monto'),
                'totalEgresos' => $movimientos->where('tipo', 'egreso')->sum('monto'),
            ]);

            return $pdf->stream("balance-{$validated['desde']}-a-{$validated['hasta']}.pdf");
        }, 'No se pudo generar el balance.');
    }

    /**
     * Art. 5° inc. f) y g) del Decreto 4767/72: nómina de la Comisión
     * Directiva y de la Comisión Revisora de Cuentas vigentes.
     */
    public function nominaPdf(): Response
    {
        return $this->ejecutarRespuesta(function () {
            $pdf = Pdf::loadView('reportes.pdf.nomina', [
                'establecimiento' => Establecimiento::actual(),
                'miembros' => MiembroComision::vigentes()->with('socio')->get(),
                'fecha' => now(),
            ]);

            return $pdf->stream('nomina-autoridades.pdf');
        }, 'No se pudo generar la nómina de autoridades.');
    }

    public function memoriaEdit(Asamblea $asamblea): View
    {
        return $this->ejecutarVista(
            fn () => view('reportes.memoria', ['asamblea' => $asamblea]),
            'No se pudo abrir la Memoria de la Asamblea.'
        );
    }

    public function memoriaUpdate(Request $request, Asamblea $asamblea): RedirectResponse
    {
        $validated = $request->validate([
            'memoria' => ['required', 'string'],
        ]);

        return $this->ejecutar(function () use ($validated, $asamblea) {
            $asamblea->update($validated);

            return redirect()->route('reportes.index')->with('status', 'memoria-guardada');
        }, 'No se pudo guardar la Memoria.');
    }

    /**
     * Art. 44° del Estatuto: rendición anual completa (Memoria + Balance +
     * Nómina de autoridades + referencia al Acta) para elevar a la
     * Dirección de Cooperación Escolar.
     */
    public function rendicionPdf(Asamblea $asamblea): Response
    {
        return $this->ejecutarRespuesta(function () use ($asamblea) {
            $asambleaAnterior = Asamblea::where('tipo', 'ordinaria')
                ->where('estado', 'realizada')
                ->where('fecha', '<', $asamblea->fecha)
                ->orderByDesc('fecha')
                ->first();

            $desde = $asambleaAnterior?->fecha->toDateString() ?? $asamblea->fecha->copy()->subYear()->toDateString();
            $hasta = $asamblea->fecha->toDateString();

            $movimientos = MovimientoTesoreria::entrePeriodo($desde, $hasta)->orderBy('fecha')->get();

            $pdf = Pdf::loadView('reportes.pdf.rendicion', [
                'establecimiento' => Establecimiento::actual(),
                'asamblea' => $asamblea,
                'desde' => $desde,
                'hasta' => $hasta,
                'movimientos' => $movimientos,
                'totalIngresos' => $movimientos->where('tipo', 'ingreso')->sum('monto'),
                'totalEgresos' => $movimientos->where('tipo', 'egreso')->sum('monto'),
                'miembros' => MiembroComision::vigentes()->with('socio')->get(),
            ]);

            return $pdf->stream("rendicion-asamblea-{$asamblea->id}.pdf");
        }, 'No se pudo generar la rendición anual.');
    }
}
