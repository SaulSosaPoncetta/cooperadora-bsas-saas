<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">Nuevo usuario</h2>
    </x-slot>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('usuarios.store') }}">
                @csrf

                @include('usuarios._form')

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">Crear usuario</button>
                    <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary btn-sm">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
