<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">Asamblea {{ ucfirst($asamblea->tipo) }} — {{ $asamblea->fecha->format('d/m/Y') }}</h2>
    </x-slot>

    @if (session('status'))
        <div class="alert alert-success py-2 small">Guardado.</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger py-2 small">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body small">
            <p class="mb-1">
                <span class="text-muted">Estado:</span>
                @switch($asamblea->estado)
                    @case('convocada') <span class="badge text-bg-warning">Convocada</span> @break
                    @case('realizada') <span class="badge text-bg-success">Realizada</span> @break
                    @case('anulada') <span class="badge text-bg-secondary">Anulada</span> — {{ $asamblea->motivo_anulacion }} @break
                @endswitch
            </p>
            <p class="mb-1">
                <span class="text-muted">Convocatoria:</span> {{ $asamblea->fecha_convocatoria->format('d/m/Y') }}
                ({{ $asamblea->cumpleAnticipacionConvocatoria() ? 'cumple plazo del Estatuto' : '¡fuera de plazo!' }})
            </p>
            @if ($asamblea->motivo)
                <p class="mb-1"><span class="text-muted">Motivo:</span> {{ $asamblea->motivo }}</p>
            @endif
            <p class="mb-1"><span class="text-muted">Socios/as habilitados/as al convocar:</span> {{ $asamblea->socios_activos_habilitados }}</p>
            <p class="mb-0" style="white-space: pre-line;"><span class="text-muted">Orden del Día:</span> {{ $asamblea->orden_del_dia }}</p>
        </div>
    </div>

    @if ($asamblea->estado === 'convocada')
        @can('gestionar asambleas')
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <h3 class="h6 fw-semibold mb-3">Registrar asistencia</h3>
                    <form method="POST" action="{{ route('asambleas.asistencia', $asamblea) }}">
                        @csrf
                        <div class="row row-cols-2 row-cols-sm-3 g-2 border rounded p-3" style="max-height: 16rem; overflow-y: auto;">
                            @foreach ($socios as $socio)
                                <div class="col form-check">
                                    <input class="form-check-input" type="checkbox" name="socios_ids[]" value="{{ $socio->id }}" id="asist-{{ $socio->id }}" @checked($asamblea->asistentes->contains($socio->id))>
                                    <label class="form-check-label small" for="asist-{{ $socio->id }}">{{ $socio->nombreCompleto() }}</label>
                                </div>
                            @endforeach
                        </div>
                        <div class="form-text mt-2">
                            Quórum inicial (50%): {{ $asamblea->cumpleQuorumInicial() ? 'cumplido' : 'no cumplido — puede sesionar pasada 1 hora si iguala al número de miembros de la Comisión Directiva (Art. 30°)' }}
                        </div>
                        <x-primary-button class="mt-3">Guardar asistencia</x-primary-button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <h3 class="h6 fw-semibold mb-3">Marcar como realizada / Acta</h3>
                    <form method="POST" action="{{ route('asambleas.realizar', $asamblea) }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <x-input-label for="presidente_asamblea_id" value="Presidente/a de la Asamblea" />
                                <select id="presidente_asamblea_id" name="presidente_asamblea_id" class="form-select" required>
                                    <option value="">Seleccionar...</option>
                                    @foreach ($asamblea->asistentes as $socio)
                                        <option value="{{ $socio->id }}">{{ $socio->nombreCompleto() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <x-input-label for="secretario_actas_id" value="Secretario/a de Actas" />
                                <select id="secretario_actas_id" name="secretario_actas_id" class="form-select" required>
                                    <option value="">Seleccionar...</option>
                                    @foreach ($asamblea->asistentes as $socio)
                                        <option value="{{ $socio->id }}">{{ $socio->nombreCompleto() }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mt-3">
                            <x-input-label value="Dos Socios/as designados/as para firmar el Acta (Art. 36°)" />
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <select name="firmantes_ids[]" class="form-select" required>
                                        <option value="">Firmante 1...</option>
                                        @foreach ($asamblea->asistentes as $socio)
                                            <option value="{{ $socio->id }}">{{ $socio->nombreCompleto() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <select name="firmantes_ids[]" class="form-select" required>
                                        <option value="">Firmante 2...</option>
                                        @foreach ($asamblea->asistentes as $socio)
                                            <option value="{{ $socio->id }}">{{ $socio->nombreCompleto() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <x-input-label for="resumen_acta" value="Resumen del Acta / resoluciones adoptadas" />
                            <textarea id="resumen_acta" name="resumen_acta" rows="6" class="form-control" required></textarea>
                        </div>

                        <x-primary-button class="mt-3">Marcar como realizada</x-primary-button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <h3 class="h6 fw-semibold mb-3">Anular convocatoria (Art. 29°)</h3>
                    <form method="POST" action="{{ route('asambleas.anular', $asamblea) }}" class="d-flex align-items-end gap-3">
                        @csrf
                        <div class="flex-grow-1">
                            <x-input-label value="Motivo de la nulidad" />
                            <x-text-input name="motivo_anulacion" type="text" required />
                        </div>
                        <x-danger-button type="submit">Anular</x-danger-button>
                    </form>
                </div>
            </div>
        @endcan
    @endif

    @if ($asamblea->estado === 'realizada')
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body small">
                <p class="mb-1"><span class="text-muted">Presidente/a de la Asamblea:</span> {{ optional($asamblea->presidenteAsamblea)->nombreCompleto() }}</p>
                <p class="mb-1"><span class="text-muted">Secretario/a de Actas:</span> {{ optional($asamblea->secretarioActas)->nombreCompleto() }}</p>
                <p class="mb-1"><span class="text-muted">Firmantes designados/as:</span> {{ $asamblea->firmantes->map->nombreCompleto()->join(', ') }}</p>
                <p class="mb-1"><span class="text-muted">Asistentes:</span> {{ $asamblea->asistentes->count() }}</p>
                <p class="mb-0" style="white-space: pre-line;"><span class="text-muted">Acta:</span> {{ $asamblea->resumen_acta }}</p>
            </div>
        </div>

        @can('firmar actas')
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <h3 class="h6 fw-semibold mb-3">Rendición a la Dirección de Cooperación Escolar (Art. 44°)</h3>
                    @if ($asamblea->fecha_elevada_direccion)
                        <p class="text-success small mb-0">Elevada el {{ $asamblea->fecha_elevada_direccion->format('d/m/Y') }}.</p>
                    @else
                        @if ($asamblea->rendicionVencida())
                            <p class="text-danger small">Vencido el plazo de 15 días para elevar copia del Acta.</p>
                        @endif
                        <form method="POST" action="{{ route('asambleas.elevar', $asamblea) }}" class="d-flex align-items-end gap-3">
                            @csrf
                            <div>
                                <x-input-label value="Fecha de envío" />
                                <x-text-input name="fecha_elevada_direccion" type="date" value="{{ now()->toDateString() }}" required />
                            </div>
                            <x-primary-button>Marcar como elevada</x-primary-button>
                        </form>
                    @endif
                </div>
            </div>
        @endcan
    @endif

    <a href="{{ route('asambleas.index') }}" class="text-decoration-none text-muted">← Volver al listado</a>
</x-app-layout>
