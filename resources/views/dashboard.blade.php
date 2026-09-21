<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">Panel principal</h2>
    </x-slot>

    @can('ver tesoreria')
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h3 class="h6 fw-semibold mb-0">
                Ejercicio {{ $anio }}
                @if ($esAnioEnCurso)
                    <span class="badge text-bg-success ms-1">En curso</span>
                @else
                    <span class="badge text-bg-secondary ms-1">Cerrado</span>
                @endif
            </h3>
            <form method="GET" class="d-flex align-items-center gap-2">
                @if ($mesParam)
                    <input type="hidden" name="mes" value="{{ $mesParam }}">
                @endif
                <label for="anio" class="small text-muted mb-0">Ver año:</label>
                <select id="anio" name="anio" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                    @foreach ($aniosConDatos as $opcionAnio)
                        <option value="{{ $opcionAnio }}" @selected($opcionAnio === $anio)>{{ $opcionAnio }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-uppercase text-muted small">Recaudado en {{ $anio }}</div>
                        <div class="fs-5 fw-semibold text-success">$ {{ number_format($totalIngresosAnio, 2, ',', '.') }}</div>
                        <div class="text-muted small">{{ $cantidadPagosCuotaAnio }} pagos de cuota social ($ {{ number_format($totalCuotasAnio, 2, ',', '.') }})</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-uppercase text-muted small">Gastos en {{ $anio }}</div>
                        <div class="fs-5 fw-semibold text-danger">$ {{ number_format($totalEgresosAnio, 2, ',', '.') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-uppercase text-muted small">Saldo {{ $anio }}</div>
                        <div class="fs-5 fw-semibold {{ ($totalIngresosAnio - $totalEgresosAnio) >= 0 ? '' : 'text-danger' }}">
                            $ {{ number_format($totalIngresosAnio - $totalEgresosAnio, 2, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        @if ($esAnioEnCurso)
                            <div class="text-uppercase text-muted small">Proyección a fin de {{ $anio }}</div>
                            <div class="fs-5 fw-semibold">$ {{ number_format($proyeccionAnual, 2, ',', '.') }}</div>
                            <div class="text-muted small">estimado por ritmo de recaudación</div>
                        @else
                            <div class="text-uppercase text-muted small">Resultado final {{ $anio }}</div>
                            <div class="fs-5 fw-semibold">Ejercicio cerrado</div>
                            <div class="text-muted small">ver Reportes para el balance completo</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @can('ver socios')
            <div class="row g-3 mb-3">
                <div class="col-6 col-lg-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-uppercase text-muted small">Socios/as al día</div>
                            <div class="fs-5 fw-semibold text-success">{{ $sociosAlDia }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-uppercase text-muted small">Socios/as con deuda</div>
                            <div class="fs-5 fw-semibold {{ $sociosConDeuda > 0 ? 'text-danger' : '' }}">{{ $sociosConDeuda }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endcan
    @endcan

    @can('ver asambleas')
        <div class="row g-3">
            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <a href="{{ route('dashboard', ['mes' => $fechaMes->copy()->subMonth()->format('Y-m')]) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                            <h3 class="h6 fw-semibold mb-0 text-capitalize">{{ $fechaMes->locale('es')->translatedFormat('F Y') }}</h3>
                            <a href="{{ route('dashboard', ['mes' => $fechaMes->copy()->addMonth()->format('Y-m')]) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </div>

                        <table class="table table-bordered text-center mb-0" style="table-layout: fixed;">
                            <thead class="table-light">
                                <tr>
                                    <th>Lun</th>
                                    <th>Mar</th>
                                    <th>Mié</th>
                                    <th>Jue</th>
                                    <th>Vie</th>
                                    <th>Sáb</th>
                                    <th>Dom</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $semanas = collect($dias)->chunk(7); @endphp
                                @foreach ($semanas as $semana)
                                    <tr>
                                        @foreach ($semana as $dia)
                                            @php $asambleasDia = $asambleasMes->get($dia->format('Y-m-d')); @endphp
                                            <td class="align-top p-1 {{ $dia->month !== $fechaMes->month ? 'text-muted bg-light' : '' }} {{ $dia->isToday() ? 'border-primary border-2' : '' }}" style="height: 5.5rem;">
                                                <div class="small fw-semibold">{{ $dia->day }}</div>
                                                @if ($asambleasDia)
                                                    @foreach ($asambleasDia as $asamblea)
                                                        <a href="{{ route('asambleas.show', $asamblea) }}" class="d-block text-decoration-none">
                                                            <span class="badge d-block text-truncate {{ $asamblea->estado === 'realizada' ? 'text-bg-success' : ($asamblea->estado === 'anulada' ? 'text-bg-secondary' : 'text-bg-warning') }}" style="font-size: 0.65rem;">
                                                                {{ ucfirst($asamblea->tipo) }}
                                                            </span>
                                                        </a>
                                                    @endforeach
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white fw-semibold small">Próximas Asambleas</div>
                    <ul class="list-group list-group-flush">
                        @forelse ($proximasAsambleas as $asamblea)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <a href="{{ route('asambleas.show', $asamblea) }}" class="text-decoration-none small">
                                    {{ $asamblea->fecha->format('d/m/Y') }} — {{ ucfirst($asamblea->tipo) }}
                                </a>
                                <span class="badge text-bg-warning">Convocada</span>
                            </li>
                        @empty
                            <li class="list-group-item text-muted small">No hay asambleas próximas.</li>
                        @endforelse
                    </ul>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white fw-semibold small">Asambleas Pasadas</div>
                    <ul class="list-group list-group-flush">
                        @forelse ($asambleasPasadas as $asamblea)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <a href="{{ route('asambleas.show', $asamblea) }}" class="text-decoration-none small">
                                    {{ $asamblea->fecha->format('d/m/Y') }} — {{ ucfirst($asamblea->tipo) }}
                                </a>
                                <span class="badge {{ $asamblea->estado === 'realizada' ? 'text-bg-success' : 'text-bg-secondary' }}">
                                    {{ ucfirst($asamblea->estado) }}
                                </span>
                            </li>
                        @empty
                            <li class="list-group-item text-muted small">Sin asambleas anteriores.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    @endcan

    @can('ver tesoreria')
        <h3 class="h6 fw-semibold mt-4 mb-3">Comparativa entre años</h3>
        <div class="row g-3">
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-uppercase text-muted small mb-2">Recaudación por año</div>
                        <canvas id="graficoIngresos" height="220"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-uppercase text-muted small mb-2">Gastos por año</div>
                        <canvas id="graficoEgresos" height="220"></canvas>
                    </div>
                </div>
            </div>
            @can('ver socios')
                <div class="col-12 col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-uppercase text-muted small mb-2">Socios/as vigentes al cierre de cada año</div>
                            <canvas id="graficoSocios" height="220"></canvas>
                        </div>
                    </div>
                </div>
            @endcan
        </div>

        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const anios = @json($rangoAnios);

                    new Chart(document.getElementById('graficoIngresos'), {
                        type: 'bar',
                        data: {
                            labels: anios,
                            datasets: [{
                                label: 'Recaudado ($)',
                                data: @json($serieIngresos),
                                backgroundColor: '#198754',
                            }],
                        },
                        options: {
                            plugins: { legend: { display: false } },
                            scales: { y: { beginAtZero: true } },
                        },
                    });

                    new Chart(document.getElementById('graficoEgresos'), {
                        type: 'bar',
                        data: {
                            labels: anios,
                            datasets: [{
                                label: 'Gastos ($)',
                                data: @json($serieEgresos),
                                backgroundColor: '#dc3545',
                            }],
                        },
                        options: {
                            plugins: { legend: { display: false } },
                            scales: { y: { beginAtZero: true } },
                        },
                    });

                    @can('ver socios')
                    new Chart(document.getElementById('graficoSocios'), {
                        type: 'bar',
                        data: {
                            labels: anios,
                            datasets: [{
                                label: 'Socios/as',
                                data: @json($serieSocios),
                                backgroundColor: '#7c3aed',
                            }],
                        },
                        options: {
                            plugins: { legend: { display: false } },
                            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
                        },
                    });
                    @endcan
                });
            </script>
        @endpush
    @endcan
</x-app-layout>
