<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <h2 class="h4 fw-semibold mb-0">Socios/as</h2>

            @can('gestionar socios')
                <a href="{{ route('socios.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i>Nuevo Socio/a
                </a>
            @endcan
        </div>
    </x-slot>

    @if (session('status'))
        <div class="alert alert-success py-2 small">
            @switch(session('status'))
                @case('socio-creado') Socio/a creado/a. @break
                @case('socio-actualizado') Socio/a actualizado/a. @break
                @case('socio-dado-de-baja') Socio/a dado/a de baja. @break
                @case('socio-reactivado') Socio/a reactivado/a. @break
            @endswitch
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <x-input-label for="busqueda" value="Buscar (nombre, apellido o DNI)" />
                    <x-text-input id="busqueda" name="busqueda" type="text" value="{{ $filtros['busqueda'] ?? '' }}" />
                </div>

                <div class="col-md-3">
                    <x-input-label for="categoria" value="Categoría" />
                    <select id="categoria" name="categoria" class="form-select">
                        <option value="">Todas</option>
                        <option value="activo" @selected(($filtros['categoria'] ?? '') === 'activo')>Activo/a</option>
                        <option value="honorario" @selected(($filtros['categoria'] ?? '') === 'honorario')>Honorario/a</option>
                        <option value="adherente" @selected(($filtros['categoria'] ?? '') === 'adherente')>Adherente</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <x-input-label for="estado" value="Estado" />
                    <select id="estado" name="estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="activo" @selected(($filtros['estado'] ?? '') === 'activo')>Al día</option>
                        <option value="baja" @selected(($filtros['estado'] ?? '') === 'baja')>De baja</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-secondary btn-sm">Filtrar</button>
                    <a href="{{ route('socios.index') }}" class="btn btn-link btn-sm text-decoration-none">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Apellido y Nombre</th>
                        <th>DNI</th>
                        <th>Categoría</th>
                        <th>Ingreso</th>
                        <th>Cuotas adeudadas</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($socios as $socio)
                        <tr>
                            <td>{{ $socio->nombreCompleto() }}</td>
                            <td>{{ $socio->dni }}</td>
                            <td class="text-capitalize">{{ $socio->categoria }}</td>
                            <td>{{ $socio->fecha_ingreso->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge {{ $socio->superaLimiteDeuda() ? 'text-bg-danger' : 'text-bg-light' }}">
                                    {{ $socio->cuotas_adeudadas }}
                                </span>
                            </td>
                            <td>
                                @if ($socio->activo)
                                    <span class="badge text-bg-success">Al día</span>
                                @else
                                    <span class="badge text-bg-secondary">De baja</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @can('gestionar socios')
                                    <a href="{{ route('socios.edit', $socio) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    @if ($socio->activo)
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="collapse" data-bs-target="#baja-{{ $socio->id }}">
                                            <i class="bi bi-person-dash"></i>
                                        </button>
                                    @else
                                        <form method="POST" action="{{ route('socios.reactivar', $socio) }}" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-success">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                        </form>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                        @if ($socio->activo)
                            <tr class="collapse" id="baja-{{ $socio->id }}">
                                <td colspan="7" class="bg-danger-subtle">
                                    <form method="POST" action="{{ route('socios.baja', $socio) }}" class="d-flex align-items-end gap-3 py-2">
                                        @csrf
                                        @method('PATCH')
                                        <div class="flex-grow-1">
                                            <x-input-label value="Motivo de la baja" />
                                            <x-text-input name="motivo_baja" type="text" required />
                                        </div>
                                        <x-danger-button type="submit">Confirmar baja</x-danger-button>
                                    </form>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No hay socios/as registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $socios->links() }}
    </div>
</x-app-layout>
