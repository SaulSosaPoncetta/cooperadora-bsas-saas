<x-guest-layout>
    <div class="text-center">
        @if (session('error'))
            <div class="alert alert-danger py-2 small text-start">{{ session('error') }}</div>
        @endif

        <div class="bg-warning text-dark rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
             style="width:56px;height:56px;">
            <i class="bi bi-lock fs-3"></i>
        </div>

        @if (! $establecimiento)
            <h5 class="fw-semibold mb-2">Tu usuario no tiene una cooperadora asignada</h5>
            <p class="text-muted small mb-4">
                Contactá a quien administra tu cooperadora o a soporte para que te asocien a una institución.
            </p>
        @else
            <h5 class="fw-semibold mb-2">
                @switch($establecimiento->estado_suscripcion)
                    @case('suspendida') Cuenta suspendida @break
                    @case('cancelada') Cuenta cancelada @break
                    @default Suscripción vencida
                @endswitch
            </h5>

            <p class="text-muted small mb-1">{{ $establecimiento->nombre ?: 'Tu cooperadora' }}</p>
            <p class="text-muted small mb-4">
                El acceso al sistema está temporalmente deshabilitado. Tus datos están a salvo:
                se recuperan apenas se regularice el abono.
            </p>

            @if (auth()->user()?->esPresidente())
                <form method="POST" action="{{ route('suscripcion.pagar') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm mb-2">
                        <i class="bi bi-credit-card me-1"></i>Pagar el abono
                    </button>
                </form>
            @else
                <p class="small">Avisale al/la Presidente/a de tu cooperadora.</p>
            @endif

            @if (config('saas.email_contacto'))
                <p class="small text-muted mb-0">
                    Consultas: <a href="mailto:{{ config('saas.email_contacto') }}">{{ config('saas.email_contacto') }}</a>
                </p>
            @endif
        @endif

        <form method="POST" action="{{ route('logout') }}" class="mt-3">
            @csrf
            <button type="submit" class="btn btn-link btn-sm text-decoration-none">Cerrar sesión</button>
        </form>
    </div>
</x-guest-layout>
