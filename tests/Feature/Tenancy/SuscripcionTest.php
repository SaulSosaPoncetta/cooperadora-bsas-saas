<?php

namespace Tests\Feature\Tenancy;

use App\Models\Establecimiento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaCooperadoras;
use Tests\TestCase;

class SuscripcionTest extends TestCase
{
    use CreaCooperadoras;
    use RefreshDatabase;

    public function test_una_cooperadora_activa_accede_normalmente(): void
    {
        $this->actingAs($this->usuarioDe($this->cooperadora()))->get('/profile')->assertOk();
    }

    public function test_una_cooperadora_en_prueba_vigente_accede_y_ve_el_aviso(): void
    {
        $cooperadora = Establecimiento::factory()->enPrueba(10)->create();

        $this->actingAs($this->usuarioDe($cooperadora))
            ->get('/profile')
            ->assertOk()
            ->assertSee('período de prueba');
    }

    public function test_una_prueba_terminada_bloquea_el_acceso(): void
    {
        $cooperadora = Establecimiento::factory()->enPrueba(-1)->create();

        $this->actingAs($this->usuarioDe($cooperadora))->get('/profile')->assertStatus(402);
    }

    public function test_un_abono_vencido_dentro_de_los_dias_de_gracia_sigue_funcionando_con_aviso(): void
    {
        config(['saas.dias_gracia' => 7]);
        $cooperadora = Establecimiento::factory()->vencida(3)->create();

        $this->actingAs($this->usuarioDe($cooperadora))
            ->get('/profile')
            ->assertOk()
            ->assertSee('venció');
    }

    public function test_un_abono_vencido_fuera_de_los_dias_de_gracia_bloquea_el_acceso(): void
    {
        config(['saas.dias_gracia' => 7]);
        $cooperadora = Establecimiento::factory()->vencida(10)->create();

        $this->actingAs($this->usuarioDe($cooperadora))
            ->get('/socios')
            ->assertStatus(402)
            ->assertSee('Suscripción vencida');
    }

    public function test_una_cooperadora_suspendida_queda_bloqueada(): void
    {
        $cooperadora = Establecimiento::factory()->suspendida()->create();

        $this->actingAs($this->usuarioDe($cooperadora))->get('/dashboard')->assertStatus(402);
    }

    public function test_un_usuario_sin_cooperadora_no_accede(): void
    {
        $huerfano = User::factory()->create(['establecimiento_id' => null]);
        $huerfano->assignRole('Presidente');

        $this->actingAs($huerfano)->get('/profile')->assertStatus(403);
    }

    public function test_el_bloqueo_de_una_cooperadora_no_afecta_a_las_demas(): void
    {
        $bloqueada = Establecimiento::factory()->suspendida()->create();
        $activa = $this->cooperadora();

        $this->actingAs($this->usuarioDe($bloqueada))->get('/profile')->assertStatus(402);
        $this->actingAs($this->usuarioDe($activa))->get('/profile')->assertOk();
    }
}
