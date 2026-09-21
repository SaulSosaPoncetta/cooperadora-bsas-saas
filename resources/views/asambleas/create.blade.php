<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">Convocar Asamblea</h2>
    </x-slot>

    <div class="card border-0 shadow-sm" style="max-width: 640px;">
        <div class="card-body p-4">
            <p class="text-muted small mb-4">
                Socios/as activos/as habilitados/as para votar hoy (Art. 26°): <strong>{{ $socios_habilitados }}</strong>
            </p>

            <form method="POST" action="{{ route('asambleas.store') }}">
                @csrf

                <div class="mb-3">
                    <x-input-label for="tipo" value="Tipo de Asamblea" />
                    <select id="tipo" name="tipo" class="form-select" onchange="document.getElementById('motivo-wrap').classList.toggle('d-none', this.value !== 'extraordinaria')" required>
                        <option value="ordinaria" @selected(old('tipo') === 'ordinaria')>Ordinaria (Art. 23°)</option>
                        <option value="extraordinaria" @selected(old('tipo') === 'extraordinaria')>Extraordinaria (Art. 24°)</option>
                    </select>
                    <x-input-error :messages="$errors->get('tipo')" />
                </div>

                <div id="motivo-wrap" class="mb-3 {{ old('tipo') === 'extraordinaria' ? '' : 'd-none' }}">
                    <x-input-label for="motivo" value="Motivo de la convocatoria extraordinaria" />
                    <select id="motivo" name="motivo" class="form-select">
                        <option value="">Seleccionar...</option>
                        <option value="Solicitud del 10% de los socios/as" @selected(old('motivo') === 'Solicitud del 10% de los socios/as')>Lo solicita +10% de los/as socios/as</option>
                        <option value="Solicitud de 2 miembros titulares de la Comisión Directiva" @selected(old('motivo') === 'Solicitud de 2 miembros titulares de la Comisión Directiva')>Lo solicitan 2 miembros titulares de la CD</option>
                        <option value="Lo establece la Dirección de Cooperación Escolar" @selected(old('motivo') === 'Lo establece la Dirección de Cooperación Escolar')>Lo establece la Dirección de Cooperación Escolar</option>
                    </select>
                    <x-input-error :messages="$errors->get('motivo')" />
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <x-input-label for="fecha_convocatoria" value="Fecha de la convocatoria" />
                        <x-text-input id="fecha_convocatoria" name="fecha_convocatoria" type="date" :value="old('fecha_convocatoria', now()->toDateString())" required />
                        <x-input-error :messages="$errors->get('fecha_convocatoria')" />
                    </div>

                    <div class="col-md-6">
                        <x-input-label for="fecha" value="Fecha prevista de la Asamblea" />
                        <x-text-input id="fecha" name="fecha" type="date" :value="old('fecha')" required />
                        <x-input-error :messages="$errors->get('fecha')" />
                    </div>
                </div>

                <div class="mt-3">
                    <x-input-label for="hora" value="Hora" />
                    <x-text-input id="hora" name="hora" type="time" :value="old('hora')" />
                    <x-input-error :messages="$errors->get('hora')" />
                </div>

                <div class="mt-3">
                    <x-input-label for="orden_del_dia" value="Orden del Día" />
                    <textarea id="orden_del_dia" name="orden_del_dia" rows="5" class="form-control" required>{{ old('orden_del_dia') }}</textarea>
                    <x-input-error :messages="$errors->get('orden_del_dia')" />
                    <div class="form-text">Art. 28° y 32°: sólo puede tratarse lo consignado en el Orden del Día, salvo nulidad de la convocatoria.</div>
                </div>

                <div class="alert alert-light border small mt-3">
                    <i class="bi bi-info-circle me-1"></i>
                    Plazos de anticipación: Ordinaria, 30 días (Art. 27°). Extraordinaria, entre 5 y 15 días (Art. 24°).
                </div>

                <div class="d-flex align-items-center gap-3">
                    <x-primary-button>Convocar</x-primary-button>
                    <a href="{{ route('asambleas.index') }}" class="text-decoration-none text-muted">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
