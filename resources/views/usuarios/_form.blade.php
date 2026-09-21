@php($esEdicion = $usuario->exists)

<div class="row g-3">
    <div class="col-md-6">
        <x-input-label for="name" value="Nombre y apellido" />
        <x-text-input id="name" name="name" type="text" :value="old('name', $usuario->name)" required />
        <x-input-error :messages="$errors->get('name')" />
    </div>

    <div class="col-md-6">
        <x-input-label for="email" value="Email (para ingresar)" />
        <x-text-input id="email" name="email" type="email" :value="old('email', $usuario->email)" required />
        <x-input-error :messages="$errors->get('email')" />
    </div>

    <div class="col-md-6">
        <x-input-label for="rol" value="Rol" />
        @if ($esEdicion && $usuario->esPresidente())
            <input type="text" class="form-control" value="Presidente" disabled>
            <div class="form-text">Para cambiar al/la Presidente/a usá “Transferir presidencia” en el listado de usuarios.</div>
        @else
            <select id="rol" name="rol" class="form-select" required>
                <option value="">Seleccionar…</option>
                @foreach ($roles as $rol)
                    <option value="{{ $rol }}" @selected(old('rol', $usuario->roles->first()?->name) === $rol)>{{ $rol }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('rol')" />
        @endif
    </div>

    <div class="col-md-6"></div>

    <div class="col-md-6">
        <x-input-label for="password" :value="$esEdicion ? 'Nueva contraseña (dejar vacío para no cambiarla)' : 'Contraseña'" />
        <x-text-input id="password" name="password" type="password" :required="! $esEdicion" autocomplete="new-password" />
        <x-input-error :messages="$errors->get('password')" />
    </div>

    <div class="col-md-6">
        <x-input-label for="password_confirmation" value="Confirmar contraseña" />
        <x-text-input id="password_confirmation" name="password_confirmation" type="password" :required="! $esEdicion" autocomplete="new-password" />
    </div>
</div>
