<section>
    <header>
        <h2 class="h5 fw-semibold">Información del Perfil</h2>
        <p class="text-muted small">Actualizá tu nombre y dirección de email.</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-3">
        @csrf
        @method('patch')

        <div class="mb-3">
            <x-input-label for="name" value="Nombre" />
            <x-text-input id="name" name="name" type="text" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div class="mb-3">
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" name="email" type="email" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="small mb-1">
                        Tu dirección de email no está verificada.
                        <button form="send-verification" class="btn btn-link btn-sm p-0 align-baseline">
                            Reenviar email de verificación.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="small text-success mb-0">
                            Se envió un nuevo enlace de verificación a tu email.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="d-flex align-items-center gap-3">
            <x-primary-button>Guardar</x-primary-button>

            @if (session('status') === 'profile-updated')
                <span class="text-success small">Guardado.</span>
            @endif
        </div>
    </form>
</section>
