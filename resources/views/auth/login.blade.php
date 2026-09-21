<x-guest-layout>
    <h5 class="fw-semibold mb-4">Iniciar sesión</h5>

    <x-auth-session-status class="mb-3" :status="session('status')" />

    @if ($errors->any())
        <div class="alert alert-danger py-2 mb-3">
            @foreach ($errors->all() as $error)
                <div class="small"><i class="bi bi-x-circle me-1"></i>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
        </div>

        <div class="mb-3">
            <x-input-label for="password" value="Contraseña" />
            <div class="input-group">
                <input type="password" name="password" id="password"
                       class="form-control"
                       required autocomplete="current-password">
                <button class="btn btn-outline-secondary" type="button" onclick="togglePass()" title="Ver contraseña">
                    <i class="bi bi-eye" id="ico-eye"></i>
                </button>
            </div>
        </div>

        <div class="mb-3 form-check">
            <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
            <label for="remember_me" class="form-check-label">Recordarme</label>
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-box-arrow-in-right me-2"></i>Ingresar
            </button>
        </div>
    </form>

    <div class="mt-3 text-center small text-muted">
        @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" class="text-decoration-none">
                ¿Olvidaste tu contraseña?
            </a>
        @endif
    </div>

    @if (Route::has('register'))
        <div class="text-center mt-2 small text-muted">
            ¿No tenés cuenta?
            <a href="{{ route('register') }}" class="text-decoration-none">Registrarse</a>
        </div>
    @endif

    <script>
        function togglePass() {
            const p = document.getElementById('password');
            const ico = document.getElementById('ico-eye');
            if (p.type === 'password') {
                p.type = 'text';
                ico.className = 'bi bi-eye-slash';
            } else {
                p.type = 'password';
                ico.className = 'bi bi-eye';
            }
        }
    </script>
</x-guest-layout>
