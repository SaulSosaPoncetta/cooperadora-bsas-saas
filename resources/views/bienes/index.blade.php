<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <h2 class="h4 fw-semibold mb-0">Bienes Muebles e Inmuebles</h2>

            <div class="d-flex gap-2">
                <a href="{{ route('bienes.inventario.pdf', $filtros) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-printer me-1"></i>Imprimir inventario
                </a>

                @can('gestionar bienes')
                    <a href="{{ route('bienes.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg me-1"></i>Registrar Bien
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="alert alert-success py-2 small">
            @switch(session('status'))
                @case('bien-registrado') Bien registrado. @break
                @case('bien-cedido') Cesión registrada. @break
                @case('bien-devuelto') Bien vuelto a uso interno. @break
                @case('bien-dado-de-baja') Bien dado de baja. @break
            @endswitch
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
    @endif

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="text-uppercase text-muted small">Valor estimado del patrimonio vigente</div>
            <div class="fs-5 fw-semibold">$ {{ number_format($valorTotalEnUso, 2, ',', '.') }}</div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <x-input-label for="tipo" value="Tipo" />
                    <select id="tipo" name="tipo" class="form-select">
                        <option value="">Todos</option>
                        <option value="mueble" @selected(($filtros['tipo'] ?? '') === 'mueble')>Mueble</option>
                        <option value="inmueble" @selected(($filtros['tipo'] ?? '') === 'inmueble')>Inmueble</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <x-input-label for="estado" value="Estado" />
                    <select id="estado" name="estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="en_uso" @selected(($filtros['estado'] ?? '') === 'en_uso')>En uso</option>
                        <option value="cedido" @selected(($filtros['estado'] ?? '') === 'cedido')>Cedido</option>
                        <option value="dado_de_baja" @selected(($filtros['estado'] ?? '') === 'dado_de_baja')>Dado de baja</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-secondary btn-sm">Filtrar</button>
                    <a href="{{ route('bienes.index') }}" class="btn btn-link btn-sm text-decoration-none">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Descripción</th>
                        <th>Tipo</th>
                        <th>Adquisición</th>
                        <th class="text-end">Valor</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bienes as $bien)
                        <tr>
                            <td>{{ $bien->descripcion }}</td>
                            <td class="text-capitalize">{{ $bien->tipo }}</td>
                            <td>{{ $bien->fecha_adquisicion->format('d/m/Y') }}</td>
                            <td class="text-end">{{ $bien->valor_estimado ? '$ '.number_format($bien->valor_estimado, 2, ',', '.') : '—' }}</td>
                            <td>
                                @switch($bien->estado)
                                    @case('en_uso') <span class="badge text-bg-success">En uso</span> @break
                                    @case('cedido')
                                        <span class="badge text-bg-warning">Cedido a {{ $bien->cedido_a }}</span>
                                        @if ($bien->requiere_aprobacion_dgcye)
                                            <div class="text-danger small">Requiere anuencia DGCyE (Art. 16° inc. d)</div>
                                        @endif
                                    @break
                                    @case('dado_de_baja') <span class="badge text-bg-secondary">De baja ({{ $bien->tipo_baja }})</span> @break
                                @endswitch
                            </td>
                            <td class="text-end">
                                @can('gestionar bienes')
                                    @if ($bien->estado === 'en_uso')
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#ceder-{{ $bien->id }}">Ceder</button>
                                        @if ($bien->puedeDarseDeBaja())
                                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="collapse" data-bs-target="#baja-{{ $bien->id }}">Dar de baja</button>
                                        @endif
                                    @elseif ($bien->estado === 'cedido')
                                        <form method="POST" action="{{ route('bienes.devolver', $bien) }}" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-success">Registrar devolución</button>
                                        </form>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                        @can('gestionar bienes')
                            @if ($bien->estado === 'en_uso')
                                <tr class="collapse" id="ceder-{{ $bien->id }}">
                                    <td colspan="6" class="bg-primary-subtle">
                                        <form method="POST" action="{{ route('bienes.ceder', $bien) }}" class="row g-2 align-items-end py-2">
                                            @csrf
                                            @method('PATCH')
                                            <div class="col-6 col-md-2">
                                                <x-input-label value="Destino" />
                                                <select name="destino_cesion" class="form-select form-select-sm" required>
                                                    <option value="otra_cooperadora">Otra Cooperadora</option>
                                                    <option value="escuela">La Escuela</option>
                                                    <option value="institucion_bien_publico">Institución de bien público</option>
                                                </select>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <x-input-label value="Cedido a" />
                                                <x-text-input name="cedido_a" type="text" class="form-control-sm" required />
                                            </div>
                                            <div class="col-6 col-md-2">
                                                <x-input-label value="Fecha" />
                                                <x-text-input name="fecha_cesion" type="date" class="form-control-sm" value="{{ now()->toDateString() }}" required />
                                            </div>
                                            <div class="col-6 col-md-2">
                                                <x-input-label value="Días previstos" />
                                                <x-text-input name="dias_previstos" type="number" min="0" class="form-control-sm" />
                                            </div>
                                            <div class="col-12 col-md-3">
                                                <button type="submit" class="btn btn-sm btn-primary">Confirmar</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @endif

                            @if ($bien->puedeDarseDeBaja())
                                <tr class="collapse" id="baja-{{ $bien->id }}">
                                    <td colspan="6" class="bg-danger-subtle">
                                        <form method="POST" action="{{ route('bienes.baja', $bien) }}" class="row g-2 align-items-end py-2">
                                            @csrf
                                            @method('PATCH')
                                            <div class="col-6 col-md-2">
                                                <x-input-label value="Tipo de baja" />
                                                <select name="tipo_baja" class="form-select form-select-sm" required>
                                                    <option value="venta">Venta (2/3 CD)</option>
                                                    <option value="donacion">Donación (aprob. DGCyE)</option>
                                                </select>
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <x-input-label value="Motivo" />
                                                <x-text-input name="motivo_baja" type="text" class="form-control-sm" required />
                                            </div>
                                            <div class="col-6 col-md-2">
                                                <x-input-label value="Fecha" />
                                                <x-text-input name="fecha_baja" type="date" class="form-control-sm" value="{{ now()->toDateString() }}" required />
                                            </div>
                                            <div class="col-12 col-md-2 form-check">
                                                <input type="checkbox" class="form-check-input" name="aprobacion_baja" value="1" id="aprob-{{ $bien->id }}" required>
                                                <label class="form-check-label small" for="aprob-{{ $bien->id }}">Cuenta con la aprobación requerida (Art. 17°)</label>
                                            </div>
                                            <div class="col-12 col-md-2">
                                                <button type="submit" class="btn btn-sm btn-danger">Confirmar baja</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @endif
                        @endcan
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No hay bienes registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $bienes->links() }}
    </div>
</x-app-layout>
