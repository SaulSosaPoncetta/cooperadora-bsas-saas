<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">Registrar Bien</h2>
    </x-slot>

    <div class="card border-0 shadow-sm" style="max-width: 640px;">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('bienes.store') }}">
                @csrf

                <div class="mb-3">
                    <x-input-label for="tipo" value="Tipo" />
                    <select id="tipo" name="tipo" class="form-select" required>
                        <option value="mueble" @selected(old('tipo') === 'mueble')>Mueble</option>
                        <option value="inmueble" @selected(old('tipo') === 'inmueble')>Inmueble</option>
                    </select>
                    <x-input-error :messages="$errors->get('tipo')" />
                    <div class="form-text">Los inmuebles ingresan al patrimonio fiscal, afectados a la Dirección Gral. de Cultura y Educación (Art. 18°).</div>
                </div>

                <div class="mb-3">
                    <x-input-label for="descripcion" value="Descripción" />
                    <x-text-input id="descripcion" name="descripcion" type="text" :value="old('descripcion')" required autofocus />
                    <x-input-error :messages="$errors->get('descripcion')" />
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <x-input-label for="fecha_adquisicion" value="Fecha de adquisición" />
                        <x-text-input id="fecha_adquisicion" name="fecha_adquisicion" type="date" :value="old('fecha_adquisicion', now()->toDateString())" required />
                        <x-input-error :messages="$errors->get('fecha_adquisicion')" />
                    </div>

                    <div class="col-md-6">
                        <x-input-label for="valor_estimado" value="Valor estimado ($)" />
                        <x-text-input id="valor_estimado" name="valor_estimado" type="number" step="0.01" min="0" :value="old('valor_estimado')" />
                        <x-input-error :messages="$errors->get('valor_estimado')" />
                    </div>
                </div>

                <div class="mt-3">
                    <x-input-label for="origen" value="Origen" />
                    <select id="origen" name="origen" class="form-select" required>
                        @foreach (\App\Models\Bien::ORIGENES as $valor => $etiqueta)
                            <option value="{{ $valor }}" @selected(old('origen') === $valor)>{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('origen')" />
                </div>

                <div class="d-flex align-items-center gap-3 mt-4">
                    <x-primary-button>Guardar</x-primary-button>
                    <a href="{{ route('bienes.index') }}" class="text-decoration-none text-muted">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
