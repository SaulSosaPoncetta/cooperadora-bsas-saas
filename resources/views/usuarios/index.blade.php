<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <h2 class="h4 fw-semibold mb-0">Usuarios de la cooperadora</h2>

            <a href="{{ route('usuarios.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i>Nuevo usuario
            </a>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="alert alert-success py-2 small">
            @switch(session('status'))
                @case('usuario-creado') Usuario creado. @break
                @case('usuario-actualizado') Usuario actualizado. @break
                @case('usuario-eliminado') Usuario eliminado. @break
            @endswitch
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($usuarios as $u)
                        <tr>
                            <td>{{ $u->name }}</td>
                            <td>{{ $u->email }}</td>
                            <td>
                                <span class="badge {{ $u->esPresidente() ? 'bg-primary' : 'bg-secondary' }}">
                                    {{ $u->roles->first()?->name ?? 'Sin rol' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('usuarios.edit', $u->id) }}" class="btn btn-sm btn-outline-secondary">Editar</a>

                                @if (! $u->esPresidente())
                                    <form method="POST" action="{{ route('usuarios.destroy', $u->id) }}" class="d-inline"
                                          onsubmit="return confirm('¿Eliminar a {{ $u->name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h3 class="h6 fw-semibold">Transferir presidencia</h3>
            <p class="text-muted small">
                Cuando cambia la Comisión Directiva, el nuevo Presidente pasa a administrar la cooperadora
                y vos quedás con el rol que elijas.
            </p>

            <form method="POST" action="{{ route('usuarios.presidencia') }}" class="row g-3 align-items-end">
                @csrf

                <div class="col-md-4">
                    <x-input-label for="nuevo_presidente_id" value="Nuevo/a Presidente/a" />
                    <select id="nuevo_presidente_id" name="nuevo_presidente_id" class="form-select" required>
                        <option value="">Seleccionar…</option>
                        @foreach ($usuarios->where('id', '!=', auth()->id()) as $u)
                            <option value="{{ $u->id }}" @selected(old('nuevo_presidente_id') == $u->id)>{{ $u->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('nuevo_presidente_id')" />
                </div>

                <div class="col-md-3">
                    <x-input-label for="rol_saliente" value="Mi nuevo rol" />
                    <select id="rol_saliente" name="rol_saliente" class="form-select" required>
                        @foreach ($roles as $rol)
                            <option value="{{ $rol }}" @selected(old('rol_saliente', 'Secretario') === $rol)>{{ $rol }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('rol_saliente')" />
                </div>

                <div class="col-md-3">
                    <x-input-label for="password_transfer" value="Tu contraseña" />
                    <x-text-input id="password_transfer" name="password" type="password" required />
                    <x-input-error :messages="$errors->get('password')" />
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100"
                            onclick="return confirm('¿Transferir la presidencia? Perderás el acceso de administrador.');">
                        Transferir
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
