<x-guest-layout>
    <h5 class="fw-semibold mb-3">Verificá tu email</h5>

    <p class="text-muted small mb-3">
        ¡Gracias por registrarte! Antes de empezar, ¿podrías verificar tu dirección de email haciendo clic en el enlace que te acabamos de enviar? Si no recibiste el email, con gusto te enviamos otro.
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success py-2 small">
            Se envió un nuevo enlace de verificación a la dirección de email que indicaste al registrarte.
        </div>
    @endif

    <div class="d-flex align-items-center justify-content-between mt-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-primary btn-sm">Reenviar email de verificación</button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-link btn-sm text-muted text-decoration-none">Cerrar sesión</button>
        </form>
    </div>
</x-guest-layout>
