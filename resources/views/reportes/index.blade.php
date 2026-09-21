<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">Reportes y Rendiciones</h2>
    </x-slot>

    @if (session('status') === 'memoria-guardada')
        <div class="alert alert-success py-2 small">Memoria guardada.</div>
    @endif

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <h3 class="h6 fw-semibold mb-1">Balance por período</h3>
            <p class="text-muted small mb-3">Art. 9° inc. d) del Estatuto: balance con comprobantes para presentar a la Comisión Directiva o a la Asamblea.</p>
            <form method="GET" action="{{ route('reportes.balance.pdf') }}" target="_blank" class="row g-3 align-items-end">
                <div class="col-auto">
                    <x-input-label for="desde" value="Desde" />
                    <x-text-input id="desde" name="desde" type="date" required />
                </div>
                <div class="col-auto">
                    <x-input-label for="hasta" value="Hasta" />
                    <x-text-input id="hasta" name="hasta" type="date" required />
                </div>
                <div class="col-auto">
                    <x-primary-button type="submit">Generar PDF</x-primary-button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <h3 class="h6 fw-semibold mb-1">Nómina de autoridades vigente</h3>
            <p class="text-muted small mb-3">Comisión Directiva actual, para acompañar trámites ante la Dirección de Cooperación Escolar.</p>
            <a href="{{ route('reportes.nomina.pdf') }}" target="_blank" class="btn btn-primary btn-sm">Generar PDF</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h3 class="h6 fw-semibold mb-1">Rendición anual (Art. 44°)</h3>
            <p class="text-muted small mb-3">Memoria + Balance del período + Nómina de autoridades, para cada Asamblea Ordinaria realizada.</p>

            <div class="list-group list-group-flush">
                @forelse ($asambleasOrdinarias as $asamblea)
                    <div class="list-group-item d-flex align-items-center justify-content-between px-0">
                        <div>
                            <div class="fw-semibold small">Asamblea Ordinaria — {{ $asamblea->fecha->format('d/m/Y') }}</div>
                            <div class="text-muted small">{{ $asamblea->memoria ? 'Memoria cargada' : 'Falta cargar la Memoria' }}</div>
                        </div>
                        <div class="d-flex gap-3">
                            @can('gestionar reportes')
                                <a href="{{ route('reportes.memoria.edit', $asamblea) }}" class="small text-decoration-none">
                                    {{ $asamblea->memoria ? 'Editar Memoria' : 'Cargar Memoria' }}
                                </a>
                            @endcan
                            @if ($asamblea->memoria)
                                <a href="{{ route('reportes.rendicion.pdf', $asamblea) }}" target="_blank" class="small text-decoration-none">Generar PDF</a>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-muted small mb-0">Todavía no hay Asambleas Ordinarias realizadas.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
