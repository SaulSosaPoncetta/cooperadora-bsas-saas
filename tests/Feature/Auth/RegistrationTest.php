<?php

namespace Tests\Feature\Auth;

use App\Models\Establecimiento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function datos(array $extra = []): array
    {
        return array_merge([
            'establecimiento_nombre' => 'Escuela N° 12',
            'cue' => '060012300',
            'distrito' => 'La Plata',
            'localidad' => 'City Bell',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ], $extra);
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $this->get('/register')->assertStatus(200);
    }

    public function test_una_cooperadora_se_registra_con_su_presidente_y_periodo_de_prueba(): void
    {
        config(['saas.dias_prueba' => 30]);

        $response = $this->post('/register', $this->datos());

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $usuario = User::where('email', 'test@example.com')->firstOrFail();
        $cooperadora = Establecimiento::findOrFail($usuario->establecimiento_id);

        $this->assertSame('Escuela N° 12', $cooperadora->nombre);
        $this->assertTrue($usuario->hasRole('Presidente'));
        $this->assertSame(Establecimiento::ESTADO_PRUEBA, $cooperadora->estado_suscripcion);
        $this->assertSame(now()->addDays(30)->toDateString(), $cooperadora->prueba_hasta->toDateString());
        $this->assertTrue($cooperadora->accesoPermitido());
    }

    public function test_cada_registro_crea_una_cooperadora_distinta(): void
    {
        $this->post('/register', $this->datos());
        auth()->logout();
        $this->post('/register', $this->datos(['email' => 'otra@example.com', 'establecimiento_nombre' => 'Escuela N° 99']));

        $this->assertSame(2, Establecimiento::count());
        $this->assertNotSame(
            User::where('email', 'test@example.com')->value('establecimiento_id'),
            User::where('email', 'otra@example.com')->value('establecimiento_id'),
        );
    }

    public function test_el_nombre_del_establecimiento_es_obligatorio(): void
    {
        $this->post('/register', $this->datos(['establecimiento_nombre' => '']))
            ->assertSessionHasErrors('establecimiento_nombre');

        $this->assertGuest();
        $this->assertSame(0, Establecimiento::count());
    }

    public function test_con_el_registro_cerrado_no_se_pueden_dar_altas(): void
    {
        config(['saas.registro_abierto' => false]);

        $this->get('/register')->assertNotFound();
        $this->post('/register', $this->datos())->assertNotFound();
        $this->assertSame(0, User::count());
    }
}
