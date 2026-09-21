<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManejaExcepciones;
use App\Models\User;
use App\Support\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

/**
 * Administración de usuarios de la cooperadora. Sólo el/la Presidente/a
 * (permiso "gestionar usuarios") puede usarla, y siempre dentro de su
 * propia institución.
 */
class UsuarioController extends Controller
{
    use ManejaExcepciones;

    public function index(): View
    {
        return $this->ejecutarVista(function () {
            return view('usuarios.index', [
                'usuarios' => User::delEstablecimiento()->with('roles')->orderBy('name')->get(),
                'roles' => $this->rolesAsignables(),
            ]);
        }, 'No se pudo cargar el listado de usuarios.');
    }

    public function create(): View
    {
        return $this->ejecutarVista(fn () => view('usuarios.create', [
            'usuario' => new User,
            'roles' => $this->rolesAsignables(),
        ]), 'No se pudo abrir el formulario de alta de usuario.');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Password::defaults()],
            'rol' => ['required', Rule::in($this->rolesAsignables())],
        ]);

        return $this->ejecutarEnTransaccion(function () use ($validated) {
            $usuario = new User([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
            ]);
            $usuario->establecimiento_id = Tenant::id();
            $usuario->save();
            $usuario->assignRole($validated['rol']);

            return redirect()->route('usuarios.index')->with('status', 'usuario-creado');
        }, 'No se pudo crear el usuario.');
    }

    public function edit(int $usuario): View
    {
        // Se resuelve fuera de ejecutarVista para que un id ajeno responda 404
        // en vez de quedar atrapado como "error de conexión".
        $usuario = $this->buscar($usuario);

        return $this->ejecutarVista(fn () => view('usuarios.edit', [
            'usuario' => $usuario,
            'roles' => $this->rolesAsignables(),
        ]), 'No se pudo abrir la ficha del usuario.');
    }

    public function update(Request $request, int $usuario): RedirectResponse
    {
        $usuario = $this->buscar($usuario);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($usuario->id)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            // El rol del/la Presidente/a sólo cambia transfiriendo la presidencia.
            'rol' => [$usuario->esPresidente() ? 'nullable' : 'required', Rule::in($this->rolesAsignables())],
        ]);

        return $this->ejecutarEnTransaccion(function () use ($validated, $usuario) {
            $usuario->name = $validated['name'];
            $usuario->email = $validated['email'];

            if (! empty($validated['password'])) {
                $usuario->password = $validated['password'];
            }

            $usuario->save();

            if (! $usuario->esPresidente()) {
                $usuario->syncRoles([$validated['rol']]);
            }

            return redirect()->route('usuarios.index')->with('status', 'usuario-actualizado');
        }, 'No se pudieron guardar los cambios del usuario.');
    }

    public function destroy(Request $request, int $usuario): RedirectResponse
    {
        $usuario = $this->buscar($usuario);

        if ($usuario->id === $request->user()->id || $usuario->esPresidente()) {
            return redirect()->route('usuarios.index')
                ->with('error', 'No se puede eliminar al/la Presidente/a. Transferí la presidencia primero.');
        }

        return $this->ejecutar(function () use ($usuario) {
            $usuario->delete();

            return redirect()->route('usuarios.index')->with('status', 'usuario-eliminado');
        }, 'No se pudo eliminar el usuario.');
    }

    /**
     * Traspaso de la presidencia (cambio de Comisión Directiva): el/la
     * usuario/a elegido/a pasa a ser Presidente/a y administrador/a, y el/la
     * saliente queda con otro rol. La cooperadora nunca queda sin Presidente/a.
     */
    public function transferirPresidencia(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nuevo_presidente_id' => ['required', 'integer'],
            'rol_saliente' => ['required', Rule::in($this->rolesAsignables())],
            'password' => ['required', 'current_password'],
        ]);

        $saliente = $request->user();
        $nuevo = $this->buscar((int) $validated['nuevo_presidente_id']);

        if ($nuevo->id === $saliente->id) {
            return redirect()->route('usuarios.index')
                ->with('error', 'Elegí a otro usuario para transferir la presidencia.');
        }

        return $this->ejecutarEnTransaccion(function () use ($saliente, $nuevo, $validated) {
            $nuevo->syncRoles(['Presidente']);
            $saliente->syncRoles([$validated['rol_saliente']]);

            return redirect()->route('dashboard')->with('status', 'presidencia-transferida');
        }, 'No se pudo transferir la presidencia.');
    }

    /**
     * Sólo usuarios de la propia cooperadora: un id de otra institución
     * responde 404, igual que si no existiera.
     */
    private function buscar(int $id): User
    {
        return User::delEstablecimiento()->with('roles')->findOrFail($id);
    }

    /**
     * Roles que el/la Presidente/a puede asignar (todos menos Presidente,
     * que sólo se transfiere).
     *
     * @return array<int, string>
     */
    private function rolesAsignables(): array
    {
        return Role::query()
            ->where('name', '!=', 'Presidente')
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }
}
