<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <h2 class="h4 fw-semibold mb-0">Asambleas</h2>

            @can('gestionar asambleas')
                <a href="{{ route('asambleas.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i>Convocar Asamblea
                </a>
            @endcan
        </div>
    </x-slot>

    @if (session('status'))
        <div class="alert alert-success py-2 small">
            @switch(session('status'))
                @case('asamblea-convocada') Asamblea convocada. @break
                @case('asistencia-registrada') Asistencia registrada. @break
                @case('asamblea-realizada') Asamblea marcada como realizada. @break
                @case('asamblea-anulada') Convocatoria anulada. @break
                @case('rendicion-registrada') Rendición a la Dirección registrada. @break
            @endswitch
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Rendición a Dirección</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($asambleas as $asamblea)
                        <tr>
                            <td>{{ $asamblea->fecha->format('d/m/Y') }}</td>
                            <td class="text-capitalize">{{ $asamblea->tipo }}</td>
                            <td>
                                @switch($asamblea->estado)
                                    @case('convocada') <span class="badge text-bg-warning">Convocada</span> @break
                                    @case('realizada') <span class="badge text-bg-success">Realizada</span> @break
                                    @case('anulada') <span class="badge text-bg-secondary">Anulada</span> @break
                                @endswitch
                            </td>
                            <td>
                                @if ($asamblea->estado !== 'realizada')
                                    —
                                @elseif ($asamblea->fecha_elevada_direccion)
                                    <span class="text-success">Elevada {{ $asamblea->fecha_elevada_direccion->format('d/m/Y') }}</span>
                                @elseif ($asamblea->rendicionVencida())
                                    <span class="badge text-bg-danger">Vencida (Art. 44°)</span>
                                @else
                                    <span class="text-warning-emphasis">Pendiente — {{ $asamblea->diasRestantesRendicion() }} días</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('asambleas.show', $asamblea) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No hay asambleas registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $asambleas->links() }}
    </div>
</x-app-layout>
