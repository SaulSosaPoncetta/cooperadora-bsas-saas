<x-guest-layout>
    <h5 class="fw-semibold mb-3">Confirmar contraseña</h5>

    <p class="text-muted small mb-3">
        Esta es un área segura de la aplicación. Confirmá tu contraseña antes de continuar.
    </p>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="mb-3">
            <x-input-label for="password" value="Contraseña" />
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" autofocus />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary">Confirmar</button>
        </div>
    </form>
</x-guest-layout>
