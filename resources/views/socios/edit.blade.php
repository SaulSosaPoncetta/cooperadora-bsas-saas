<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">Editar Socio/a — {{ $socio->nombreCompleto() }}</h2>
    </x-slot>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('socios.update', $socio) }}">
                @csrf
                @method('PUT')
                @include('socios._form')

                <div class="d-flex align-items-center gap-3 mt-4">
                    <x-primary-button>Guardar</x-primary-button>
                    <a href="{{ route('socios.index') }}" class="text-decoration-none text-muted">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
