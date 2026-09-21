<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">Asignar cargo en Comisión Directiva</h2>
    </x-slot>

    <div class="card border-0 shadow-sm" style="max-width: 640px;">
        <div class="card-body p-4">

            @if ($socios->isEmpty())
                <div class="alert alert-danger py-2 small mb-0">
                    No hay socios/as elegibles: se requiere ser Activo/a con al menos 30 días de antigüedad (Art. 26° del Estatuto). Revisá el módulo de Socios.
                </div>
            @else
                <form method="POST" action="{{ route('comision.store') }}">
                    @csrf

                    <div class="mb-3">
                        <x-input-label for="socio_id" value="Socio/a" />
                        <select id="socio_id" name="socio_id" class="form-select" required>
                            <option value="">Seleccionar...</option>
                            @foreach ($socios as $socio)
                                <option value="{{ $socio->id }}" @selected(old('socio_id') == $socio->id)>{{ $socio->nombreCompleto() }} — DNI {{ $socio->dni }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('socio_id')" />
                    </div>

                    <div class="mb-3">
                        <x-input-label for="cargo" value="Cargo" />
                        <select id="cargo" name="cargo" class="form-select" required>
                            <option value="">Seleccionar...</option>
                            @foreach (\App\Models\MiembroComision::ETIQUETAS_CARGO as $valor => $etiqueta)
                                <option value="{{ $valor }}" @selected(old('cargo') === $valor) @disabled($cupos[$valor] <= 0)>
                                    {{ $etiqueta }} (cupo disponible: {{ $cupos[$valor] }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('cargo')" />
                    </div>

                    <div class="mb-3">
                        <x-input-label for="fecha_inicio" value="Fecha de inicio del mandato" />
                        <x-text-input id="fecha_inicio" name="fecha_inicio" type="date" :value="old('fecha_inicio', now()->toDateString())" required />
                        <x-input-error :messages="$errors->get('fecha_inicio')" />
                    </div>

                    <div class="alert alert-light border small mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Recordá el Art. 6° del Estatuto: los cargos de Presidente/a y Tesorero/a son incompatibles entre cónyuges o parientes hasta 2do grado de consanguinidad, y con la Comisión Revisora de Cuentas o el/la Asesor/a.
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <x-primary-button>Asignar</x-primary-button>
                        <a href="{{ route('comision.index') }}" class="text-decoration-none text-muted">Cancelar</a>
                    </div>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
