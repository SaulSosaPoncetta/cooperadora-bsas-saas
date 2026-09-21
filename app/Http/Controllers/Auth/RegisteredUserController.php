<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Establecimiento;
use App\Models\User;
use App\Services\PanelMigestion;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

/**
 * Alta autoservicio de una cooperadora: crea el establecimiento (con su
 * período de prueba) y a su primer usuario, que queda como Presidente/a
 * y, por lo tanto, administrador/a de su institución.
 */
class RegisteredUserController extends Controller
{
    public function create(): View
    {
        abort_unless(config('saas.registro_abierto'), 404);

        return view('auth.register');
    }

    public function store(Request $request, PanelMigestion $panel): RedirectResponse
    {
        abort_unless(config('saas.registro_abierto'), 404);

        $validated = $request->validate([
            'establecimiento_nombre' => ['required', 'string', 'max:255'],
            'cue' => ['nullable', 'string', 'max:50'],
            'distrito' => ['nullable', 'string', 'max:255'],
            'localidad' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = DB::transaction(function () use ($validated) {
            $establecimiento = Establecimiento::create([
                'nombre' => $validated['establecimiento_nombre'],
                'cue' => $validated['cue'] ?? null,
                'distrito' => $validated['distrito'] ?? null,
                'localidad' => $validated['localidad'] ?? null,
            ]);

            $establecimiento->forceFill([
                'estado_suscripcion' => Establecimiento::ESTADO_PRUEBA,
                'prueba_hasta' => now()->addDays((int) config('saas.dias_prueba'))->toDateString(),
            ])->save();

            $user = new User([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
            ]);
            $user->establecimiento_id = $establecimiento->id;
            $user->save();

            $user->assignRole('Presidente');

            return $user;
        });

        // Alta en MiGestión Panel para que el abono se cobre y controle. Si el
        // panel no responde, el registro igual se completa y se reintenta con
        // `php artisan panel:sincronizar --registrar`.
        $panel->registrarCliente(Establecimiento::findOrFail($user->establecimiento_id), $user);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
