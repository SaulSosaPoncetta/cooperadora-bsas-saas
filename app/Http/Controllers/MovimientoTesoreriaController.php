<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManejaExcepciones;
use App\Models\Establecimiento;
use App\Models\MiembroComision;
use App\Models\MovimientoTesoreria;
use App\Models\Socio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MovimientoTesoreriaController extends Controller
{
    use ManejaExcepciones;
    public function index(Request $request): View
    {
        return $this->ejecutarVista(function () use ($request) {
            $movimientos = MovimientoTesoreria::query()
                ->with(['socio', 'autorizante.socio'])
                ->when($request->filled('tipo'), fn ($q) => $q->where('tipo', $request->string('tipo')))
                ->entrePeriodo($request->input('desde'), $request->input('hasta'))
                ->orderByDesc('fecha')
                ->orderByDesc('id')
                ->paginate(20)
                ->withQueryString();

            $totalIngresos = MovimientoTesoreria::ingresos()->entrePeriodo($request->input('desde'), $request->input('hasta'))->sum('monto');
            $totalEgresos = MovimientoTesoreria::egresos()->entrePeriodo($request->input('desde'), $request->input('hasta'))->sum('monto');

            return view('tesoreria.index', [
                'movimientos' => $movimientos,
                'filtros' => $request->only(['tipo', 'desde', 'hasta']),
                'totalIngresos' => $totalIngresos,
                'totalEgresos' => $totalEgresos,
                'saldo' => $totalIngresos - $totalEgresos,
                'establecimiento' => Establecimiento::actual(),
            ]);
        }, 'No se pudo cargar el libro de Tesorería.');
    }

    public function create(Request $request): View
    {
        return $this->ejecutarVista(function () use ($request) {
            $tipo = $request->query('tipo', 'ingreso');

            return view('tesoreria.create', [
                'tipo' => $tipo,
                'categorias' => $tipo === 'egreso' ? MovimientoTesoreria::CATEGORIAS_EGRESO : MovimientoTesoreria::CATEGORIAS_INGRESO,
                'socios' => Socio::activos()->orderBy('apellido')->get(),
                'firmantes' => MiembroComision::vigentes()
                    ->whereIn('cargo', ['presidente', 'secretario', 'tesorero'])
                    ->with('socio')
                    ->get(),
            ]);
        }, 'No se pudo abrir el formulario de movimiento.');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fecha' => ['required', 'date'],
            'tipo' => ['required', 'in:ingreso,egreso'],
            'categoria' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($request) {
                    $categoriasValidas = $request->input('tipo') === 'egreso'
                        ? array_keys(MovimientoTesoreria::CATEGORIAS_EGRESO)
                        : array_keys(MovimientoTesoreria::CATEGORIAS_INGRESO);

                    if (! in_array($value, $categoriasValidas)) {
                        $fail('La categoría no corresponde al tipo de movimiento seleccionado.');
                    }
                },
            ],
            'concepto' => ['required', 'string', 'max:255'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'comprobante_numero' => ['nullable', 'string', 'max:100'],
            'socio_id' => ['nullable', Rule::exists('socios', 'id')],
            // Art. 14° del Estatuto: la extracción de fondos requiere la
            // firma de dos de entre Presidente/a, Secretario/a y Tesorero/a.
            'autorizado_por' => [
                'required_if:tipo,egreso',
                'nullable',
                Rule::exists('miembros_comision', 'id'),
            ],
        ]);

        $validated['registrado_por'] = $request->user()->id;

        return $this->ejecutarEnTransaccion(function () use ($validated) {
            $movimiento = MovimientoTesoreria::create($validated);

            // Si el ingreso corresponde al pago de una cuota social, se
            // descuenta una cuota adeudada del socio (Art. 22° del Estatuto).
            // Si el pago cubre más de una cuota, puede ajustarse manualmente
            // desde la ficha del socio. Se bloquea la fila del socio durante
            // la transacción para evitar descuentos duplicados si se
            // registran dos pagos casi al mismo tiempo.
            if ($movimiento->tipo === 'ingreso' && $movimiento->categoria === 'cuota_social' && $movimiento->socio_id) {
                $socio = Socio::where('id', $movimiento->socio_id)->lockForUpdate()->first();

                if ($socio && $socio->cuotas_adeudadas > 0) {
                    $socio->decrement('cuotas_adeudadas');
                }
            }

            return redirect()->route('tesoreria.index')->with('status', 'movimiento-registrado');
        }, 'No se pudo registrar el movimiento de tesorería.');
    }
}
