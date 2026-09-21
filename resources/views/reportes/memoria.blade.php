<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">Memoria del ejercicio — {{ $asamblea->fecha->format('d/m/Y') }}</h2>
    </x-slot>

    <div class="card border-0 shadow-sm" style="max-width: 720px;">
        <div class="card-body p-4">
            <p class="text-muted small mb-3">
                Resumen de lo actuado por la Comisión Directiva durante el ejercicio, para ser aprobado por la Asamblea Ordinaria (Art. 23° inc. a) y elevado a la Dirección de Cooperación Escolar (Art. 44°).
            </p>

            <form method="POST" action="{{ route('reportes.memoria.update', $asamblea) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <x-input-label for="memoria" value="Memoria" />
                    <textarea id="memoria" name="memoria" rows="14" class="form-control" required>{{ old('memoria', $asamblea->memoria) }}</textarea>
                    <x-input-error :messages="$errors->get('memoria')" />
                </div>

                <div class="d-flex align-items-center gap-3">
                    <x-primary-button>Guardar</x-primary-button>
                    <a href="{{ route('reportes.index') }}" class="text-decoration-none text-muted">Volver</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
