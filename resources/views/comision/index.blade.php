<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <h2 class="h4 fw-semibold mb-0">Comisión Directiva</h2>

            @can('gestionar comision directiva')
                <a href="{{ route('comision.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i>Asignar cargo
                </a>
            @endcan
        </div>
    </x-slot>

    @if (session('status'))
        <div class="alert alert-success py-2 small">
            @switch(session('status'))
                @case('miembro-asignado') Cargo asignado. @break
                @case('miembro-cesado') Cese registrado. @break
            @endswitch
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <h3 class="h6 fw-semibold mb-3">Cupos según Art. 3° del Estatuto</h3>
            <div class="row g-3 text-center">
                @foreach ($cupos as $cargo => $info)
                    <div class="col-6 col-sm-4 col-lg-2 col-md">
                        <div class="border rounded p-2">
                            <div class="fw-semibold small">{{ \App\Models\MiembroComision::ETIQUETAS_CARGO[$cargo] }}</div>
                            <div class="text-muted">{{ $info['total'] - $info['disponibles'] }} / {{ $info['total'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Cargo</th>
                        <th>Socio/a</th>
                        <th>Inicio de mandato</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($vigentes as $miembro)
                        <tr>
                            <td>{{ $miembro->etiquetaCargo() }}</td>
                            <td>{{ $miembro->socio->nombreCompleto() }}</td>
                            <td>{{ $miembro->fecha_inicio->format('d/m/Y') }}</td>
                            <td class="text-end">
                                @can('gestionar comision directiva')
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="collapse" data-bs-target="#cese-{{ $miembro->id }}">
                                        Registrar cese
                                    </button>
                                @endcan
                            </td>
                        </tr>
                        @can('gestionar comision directiva')
                            <tr class="collapse" id="cese-{{ $miembro->id }}">
                                <td colspan="4" class="bg-danger-subtle">
                                    <form method="POST" action="{{ route('comision.cese', $miembro) }}" class="d-flex flex-wrap align-items-end gap-3 py-2">
                                        @csrf
                                        @method('PATCH')
                                        <div>
                                            <x-input-label value="Fecha de cese" />
                                            <x-text-input name="fecha_fin" type="date" value="{{ now()->toDateString() }}" required />
                                        </div>
                                        <div class="flex-grow-1">
                                            <x-input-label value="Motivo (renuncia, fin de mandato, etc.)" />
                                            <x-text-input name="motivo_cese" type="text" required />
                                        </div>
                                        <x-danger-button type="submit">Confirmar</x-danger-button>
                                    </form>
                                </td>
                            </tr>
                        @endcan
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Todavía no hay miembros asignados a la Comisión Directiva.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">Historial</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Cargo</th>
                        <th>Socio/a</th>
                        <th>Período</th>
                        <th>Motivo de cese</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($historial as $miembro)
                        <tr>
                            <td>{{ $miembro->etiquetaCargo() }}</td>
                            <td>{{ $miembro->socio->nombreCompleto() }}</td>
                            <td>{{ $miembro->fecha_inicio->format('d/m/Y') }} — {{ $miembro->fecha_fin->format('d/m/Y') }}</td>
                            <td>{{ $miembro->motivo_cese }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Sin registros históricos.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $historial->links() }}
    </div>
</x-app-layout>
