<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManejaExcepciones;
use App\Models\Asamblea;
use App\Models\MovimientoTesoreria;
use App\Models\Socio;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use ManejaExcepciones;

    public function index(Request $request): View
    {
        return $this->ejecutarVista(function () use ($request) {
            $anioActual = now()->year;

        $aniosConDatos = MovimientoTesoreria::selectRaw('YEAR(fecha) as anio')
            ->distinct()
            ->pluck('anio')
            ->push($anioActual)
            ->unique()
            ->sortDesc()
            ->values();

        $anio = (int) $request->query('anio', $anioActual);
        if (! $aniosConDatos->contains($anio)) {
            $anio = $anioActual;
        }

        $movimientosAnio = MovimientoTesoreria::whereYear('fecha', $anio)->get();
        $totalIngresosAnio = $movimientosAnio->where('tipo', 'ingreso')->sum('monto');
        $totalEgresosAnio = $movimientosAnio->where('tipo', 'egreso')->sum('monto');
        $totalCuotasAnio = $movimientosAnio->where('tipo', 'ingreso')->where('categoria', 'cuota_social')->sum('monto');
        $cantidadPagosCuotaAnio = $movimientosAnio->where('tipo', 'ingreso')->where('categoria', 'cuota_social')->count();

        $esAnioEnCurso = $anio === $anioActual;
        $proyeccionAnual = null;
        if ($esAnioEnCurso) {
            $diasTranscurridos = max(1, now()->dayOfYear);
            $diasTotalesAnio = now()->daysInYear;
            $proyeccionAnual = ($totalIngresosAnio / $diasTranscurridos) * $diasTotalesAnio;
        }

        $sociosAlDia = Socio::activos()->where('cuotas_adeudadas', 0)->count();
        $sociosConDeuda = Socio::activos()->where('cuotas_adeudadas', '>', 0)->count();

        // Calendario del mes (parámetro ?mes=YYYY-MM, por defecto el actual)
        $mesParam = $request->query('mes');
        $fechaMes = $mesParam
            ? Carbon::createFromFormat('Y-m-d', $mesParam.'-01')->startOfMonth()
            : now()->startOfMonth();

        $asambleasMes = Asamblea::whereYear('fecha', $fechaMes->year)
            ->whereMonth('fecha', $fechaMes->month)
            ->get()
            ->groupBy(fn (Asamblea $a) => $a->fecha->format('Y-m-d'));

        $inicioGrilla = $fechaMes->copy()->startOfWeek(Carbon::MONDAY);
        $finGrilla = $fechaMes->copy()->endOfMonth()->endOfWeek(Carbon::MONDAY);

        $dias = [];
        $cursor = $inicioGrilla->copy();
        while ($cursor->lte($finGrilla)) {
            $dias[] = $cursor->copy();
            $cursor->addDay();
        }

        $proximasAsambleas = Asamblea::where('fecha', '>=', now()->toDateString())
            ->orderBy('fecha')
            ->limit(5)
            ->get();

        $asambleasPasadas = Asamblea::where('fecha', '<', now()->toDateString())
            ->orderByDesc('fecha')
            ->limit(5)
            ->get();

        // Series históricas por año, para los gráficos comparativos
        $primerAnioMovimiento = MovimientoTesoreria::min('fecha');
        $primerAnioSocio = Socio::min('fecha_ingreso');
        $anioMinimo = min(array_filter([
            $primerAnioMovimiento ? \Carbon\Carbon::parse($primerAnioMovimiento)->year : $anioActual,
            $primerAnioSocio ? \Carbon\Carbon::parse($primerAnioSocio)->year : $anioActual,
            $anioActual,
        ]));

        $rangoAnios = range($anioMinimo, $anioActual);

        $serieIngresos = [];
        $serieEgresos = [];
        $serieSocios = [];

        foreach ($rangoAnios as $y) {
            $finDeAnio = \Carbon\Carbon::createFromDate($y, 12, 31)->endOfDay();

            $serieIngresos[] = (float) MovimientoTesoreria::whereYear('fecha', $y)->where('tipo', 'ingreso')->sum('monto');
            $serieEgresos[] = (float) MovimientoTesoreria::whereYear('fecha', $y)->where('tipo', 'egreso')->sum('monto');
            $serieSocios[] = Socio::where('fecha_ingreso', '<=', $finDeAnio)
                ->where(function ($query) use ($finDeAnio) {
                    $query->whereNull('fecha_baja')->orWhere('fecha_baja', '>', $finDeAnio);
                })
                ->count();
        }

        return view('dashboard', [
            'totalIngresosAnio' => $totalIngresosAnio,
            'totalEgresosAnio' => $totalEgresosAnio,
            'totalCuotasAnio' => $totalCuotasAnio,
            'cantidadPagosCuotaAnio' => $cantidadPagosCuotaAnio,
            'proyeccionAnual' => $proyeccionAnual,
            'esAnioEnCurso' => $esAnioEnCurso,
            'sociosAlDia' => $sociosAlDia,
            'sociosConDeuda' => $sociosConDeuda,
            'anio' => $anio,
            'aniosConDatos' => $aniosConDatos,
            'mesParam' => $mesParam,
            'fechaMes' => $fechaMes,
            'dias' => $dias,
            'asambleasMes' => $asambleasMes,
            'proximasAsambleas' => $proximasAsambleas,
            'asambleasPasadas' => $asambleasPasadas,
            'rangoAnios' => $rangoAnios,
            'serieIngresos' => $serieIngresos,
            'serieEgresos' => $serieEgresos,
            'serieSocios' => $serieSocios,
        ]);
        }, 'No se pudo cargar el panel principal.');
    }
}
