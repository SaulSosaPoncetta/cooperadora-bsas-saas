<?php

namespace Tests\Feature\Tenancy;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaCooperadoras;
use Tests\TestCase;

class UsuariosDeLaCooperadoraTest extends TestCase
{
    use CreaCooperadoras;
    use RefreshDatabase;

    public function test_el_presidente_crea_usuarios_dentro_de_su_cooperadora(): void
    {
        $a = $this->cooperadora();
        $presidente = $this->usuarioDe($a);

        $this->actingAs($presidente)->post('/usuarios', [
            'name' => 'Tomás Tesorero',
            'email' => 'tomas@example.com',
            'password' => 'clave-segura-123',
            'password_confirmation' => 'clave-segura-123',
            'rol' => 'Tesorero',
        ])->assertRedirect(route('usuarios.index'));

        $nuevo = User::where('email', 'tomas@example.com')->firstOrFail();
        $this->assertSame($a->id, $nuevo->establecimiento_id);
        $this->assertTrue($nuevo->hasRole('Tesorero'));
    }

    public function test_no_se_puede_crear_otro_presidente_desde_el_alta_de_usuarios(): void
    {
        $this->actingAs($this->usuarioDe($this->cooperadora()))->post('/usuarios', [
            'name' => 'Falso Presidente',
            'email' => 'falso@example.com',
            'password' => 'clave-segura-123',
            'password_confirmation' => 'clave-segura-123',
            'rol' => 'Presidente',
        ])->assertSessionHasErrors('rol');

        $this->assertDatabaseMissing('users', ['email' => 'falso@example.com']);
    }

    public function test_solo_el_presidente_administra_usuarios(): void
    {
        $a = $this->cooperadora();

        foreach (['Secretario', 'Tesorero', 'Vocal Titular', 'Revisor de Cuentas', 'Asesor'] as $rol) {
            $this->actingAs($this->usuarioDe($a, $rol))->get('/usuarios')->assertForbidden();
        }
    }

    public function test_el_presidente_solo_ve_y_toca_usuarios_de_su_cooperadora(): void
    {
        $a = $this->cooperadora();
        $b = $this->cooperadora();
        $presidenteA = $this->usuarioDe($a);
        $usuarioB = $this->usuarioDe($b, 'Secretario');
        $usuarioB->update(['name' => 'Bernardo Ajeno']);

        $this->actingAs($presidenteA)->get('/usuarios')->assertOk()->assertDontSee('Bernardo Ajeno');
        $this->actingAs($presidenteA)->get("/usuarios/{$usuarioB->id}/editar")->assertNotFound();
        $this->actingAs($presidenteA)->put("/usuarios/{$usuarioB->id}", [
            'name' => 'X', 'email' => 'x@example.com', 'rol' => 'Asesor',
        ])->assertNotFound();
        $this->actingAs($presidenteA)->delete("/usuarios/{$usuarioB->id}")->assertNotFound();

        $this->assertDatabaseHas('users', ['id' => $usuarioB->id]);
    }

    public function test_el_presidente_puede_editar_y_eliminar_a_un_usuario_propio(): void
    {
        $a = $this->cooperadora();
        $presidente = $this->usuarioDe($a);
        $secretario = $this->usuarioDe($a, 'Secretario');

        $this->actingAs($presidente)->put("/usuarios/{$secretario->id}", [
            'name' => 'Sandra Secretaria',
            'email' => $secretario->email,
            'rol' => 'Vocal Titular',
        ])->assertSessionHasNoErrors();

        $this->assertTrue($secretario->fresh()->hasRole('Vocal Titular'));

        $this->actingAs($presidente)->delete("/usuarios/{$secretario->id}")->assertRedirect(route('usuarios.index'));
        $this->assertDatabaseMissing('users', ['id' => $secretario->id]);
    }

    public function test_el_presidente_no_puede_eliminarse_ni_eliminar_su_cuenta(): void
    {
        $presidente = $this->usuarioDe($this->cooperadora());

        $this->actingAs($presidente)->delete("/usuarios/{$presidente->id}")->assertSessionHas('error');

        $this->actingAs($presidente)->delete('/profile', ['password' => 'password'])
            ->assertSessionHasErrorsIn('userDeletion', 'password');

        $this->assertDatabaseHas('users', ['id' => $presidente->id]);
    }

    public function test_la_presidencia_se_transfiere_a_otro_usuario_de_la_cooperadora(): void
    {
        $a = $this->cooperadora();
        $presidente = $this->usuarioDe($a);
        $secretario = $this->usuarioDe($a, 'Secretario');

        $this->actingAs($presidente)->post('/usuarios/presidencia', [
            'nuevo_presidente_id' => $secretario->id,
            'rol_saliente' => 'Vocal Titular',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertTrue($secretario->fresh()->hasRole('Presidente'));
        $this->assertTrue($presidente->fresh()->hasRole('Vocal Titular'));
        $this->assertFalse($presidente->fresh()->hasRole('Presidente'));
    }

    public function test_la_presidencia_no_se_puede_transferir_a_un_usuario_de_otra_cooperadora(): void
    {
        $presidente = $this->usuarioDe($this->cooperadora());
        $ajeno = $this->usuarioDe($this->cooperadora(), 'Secretario');

        $this->actingAs($presidente)->post('/usuarios/presidencia', [
            'nuevo_presidente_id' => $ajeno->id,
            'rol_saliente' => 'Secretario',
            'password' => 'password',
        ])->assertNotFound();

        $this->assertTrue($presidente->fresh()->hasRole('Presidente'));
        $this->assertFalse($ajeno->fresh()->hasRole('Presidente'));
    }

    public function test_transferir_la_presidencia_exige_la_contrasena_correcta(): void
    {
        $a = $this->cooperadora();
        $presidente = $this->usuarioDe($a);
        $secretario = $this->usuarioDe($a, 'Secretario');

        $this->actingAs($presidente)->post('/usuarios/presidencia', [
            'nuevo_presidente_id' => $secretario->id,
            'rol_saliente' => 'Secretario',
            'password' => 'incorrecta',
        ])->assertSessionHasErrors('password');

        $this->assertTrue($presidente->fresh()->hasRole('Presidente'));
    }
}
