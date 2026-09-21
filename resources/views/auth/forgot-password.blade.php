<x-guest-layout>
    <h5 class="fw-semibold mb-3">Recuperar contraseña</h5>

    <p class="text-muted small mb-3">
        ¿Olvidaste tu contraseña? No hay problema. Indicanos tu email y te enviaremos un enlace para elegir una nueva.
    </p>

    <x-auth-session-status class="mb-3" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-3">
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-envelope me-2"></i>Enviar enlace de recuperación
            </button>
        </div>
    </form>
</x-guest-layout>
