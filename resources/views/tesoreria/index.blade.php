<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <h2 class="h4 fw-semibold mb-0">Tesorería</h2>

            @can('gestionar tesoreria')
                <div class="d-flex gap-2">
                    <a href="{{ route('tesoreria.create', ['tipo' => 'ingreso']) }}" class="btn btn-success btn-sm">
                        <i class="bi bi-plus-lg me-1"></i>Nuevo Ingreso
                    </a>
                    <a href="{{ route('tesoreria.create', ['tipo' => 'egreso']) }}" class="btn btn-danger btn-sm">
                        <i class="bi bi-dash-lg me-1"></i>Nuevo Egreso
                    </a>
                </div>
            @endcan
        </div>
    </x-slot>

    @if (session('status') === 'movimiento-registrado')
        <div class="alert alert-success py-2 small">Movimiento registrado.</div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-uppercase text-muted small">Ingresos</div>
                    <div class="fs-5 fw-semibold text-success">$ {{ number_format($totalIngresos, 2, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-uppercase text-muted small">Egresos</div>
                    <div class="fs-5 fw-semibold text-danger">$ {{ number_format($totalEgresos, 2, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-uppercase text-muted small">Saldo</div>
                    <div class="fs-5 fw-semibold {{ $saldo >= 0 ? '' : 'text-danger' }}">$ {{ number_format($saldo, 2, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-uppercase text-muted small">Caja Chica autorizada</div>
                    <div class="fs-5 fw-semibold">$ {{ number_format($establecimiento->monto_caja_chica_autorizado, 2, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <x-input-label for="tipo" value="Tipo" />
                    <select id="tipo" name="tipo" class="form-select">
                        <option value="">Todos</option>
                        <option value="ingreso" @selected(($filtros['tipo'] ?? '') === 'ingreso')>Ingresos</option>
                        <option value="egreso" @selected(($filtros['tipo'] ?? '') === 'egreso')>Egresos</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <x-input-label for="desde" value="Desde" />
                    <x-text-input id="desde" name="desde" type="date" value="{{ $filtros['desde'] ?? '' }}" />
                </div>

                <div class="col-md-3">
                    <x-input-label for="hasta" value="Hasta" />
                    <x-text-input id="hasta" name="hasta" type="date" value="{{ $filtros['hasta'] ?? '' }}" />
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-secondary btn-sm">Filtrar</button>
                    <a href="{{ route('tesoreria.index') }}" class="btn btn-link btn-sm text-decoration-none">Limpiar</a>
                </div>
            </form>
            <div class="form-text mt-2">Sugerencia: filtrá por mes para armar el Balance mensual que exige el Art. 9° inc. d) del Estatuto.</div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Categoría</th>
                        <th>Concepto</th>
                        <th>Comprobante</th>
                        <th class="text-end">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($movimientos as $movimiento)
                        <tr>
                            <td>{{ $movimiento->fecha->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge {{ $movimiento->tipo === 'ingreso' ? 'text-bg-success' : 'text-bg-danger' }}">
                                    {{ $movimiento->tipo === 'ingreso' ? 'Ingreso' : 'Egreso' }}
                                </span>
                            </td>
                            <td>{{ $movimiento->etiquetaCategoria() }}</td>
                            <td>
                                {{ $movimiento->concepto }}
                                @if ($movimiento->socio)
                                    <span class="text-muted small">— {{ $movimiento->socio->nombreCompleto() }}</span>
                                @endif
                                @if ($movimiento->autorizante)
                                    <span class="text-muted small">— autoriza {{ $movimiento->autorizante->socio->nombreCompleto() }}</span>
                                @endif
                            </td>
                            <td>{{ $movimiento->comprobante_numero ?: '—' }}</td>
                            <td class="text-end {{ $movimiento->tipo === 'ingreso' ? 'text-success' : 'text-danger' }}">
                                $ {{ number_format($movimiento->monto, 2, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No hay movimientos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $movimientos->links() }}
    </div>
</x-app-layout>
