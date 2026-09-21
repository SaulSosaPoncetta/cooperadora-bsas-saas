<x-guest-layout>
    <h5 class="fw-semibold mb-1">Registrar mi cooperadora</h5>
    <p class="text-muted small mb-4">Probá el sistema sin cargo durante {{ config('saas.dias_prueba') }} días.</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <h6 class="fw-semibold text-muted small text-uppercase mb-2">Datos de la cooperadora</h6>

        <div class="mb-3">
            <x-input-label for="establecimiento_nombre" value="Establecimiento educativo" />
            <x-text-input id="establecimiento_nombre" type="text" name="establecimiento_nombre" :value="old('establecimiento_nombre')" required autofocus />
            <x-input-error :messages="$errors->get('establecimiento_nombre')" />
        </div>

        <div class="row g-2 mb-3">
            <div class="col-md-4">
                <x-input-label for="cue" value="CUE" />
                <x-text-input id="cue" type="text" name="cue" :value="old('cue')" />
                <x-input-error :messages="$errors->get('cue')" />
            </div>
            <div class="col-md-4">
                <x-input-label for="distrito" value="Distrito" />
                <x-text-input id="distrito" type="text" name="distrito" :value="old('distrito')" />
                <x-input-error :messages="$errors->get('distrito')" />
            </div>
            <div class="col-md-4">
                <x-input-label for="localidad" value="Localidad" />
                <x-text-input id="localidad" type="text" name="localidad" :value="old('localidad')" />
                <x-input-error :messages="$errors->get('localidad')" />
            </div>
        </div>

        <h6 class="fw-semibold text-muted small text-uppercase mb-2">Presidente/a (administrador/a)</h6>

        <div class="mb-3">
            <x-input-label for="name" value="Nombre y apellido" />
            <x-text-input id="name" type="text" name="name" :value="old('name')" required autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div class="mb-3">
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="mb-3">
            <x-input-label for="password" value="Contraseña" />
            <x-text-input id="password" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="mb-3">
            <x-input-label for="password_confirmation" value="Confirmar Contraseña" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <div class="d-grid mb-3">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-person-plus me-2"></i>Registrar cooperadora
            </button>
        </div>

        <div class="text-center small text-muted">
            <a href="{{ route('login') }}" class="text-decoration-none">¿Ya tenés cuenta? Ingresá</a>
        </div>
    </form>
</x-guest-layout>
