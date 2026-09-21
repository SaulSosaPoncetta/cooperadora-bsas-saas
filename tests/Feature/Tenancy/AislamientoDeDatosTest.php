<?php

namespace Tests\Feature\Tenancy;

use App\Models\Socio;
use App\Support\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\Concerns\CreaCooperadoras;
use Tests\TestCase;

class AislamientoDeDatosTest extends TestCase
{
    use CreaCooperadoras;
    use RefreshDatabase;

    public function test_el_listado_de_socios_solo_muestra_los_de_la_propia_cooperadora(): void
    {
        $a = $this->cooperadora();
        $b = $this->cooperadora();
        $this->socioEn($a, ['apellido' => 'Aguirre']);
        $this->socioEn($b, ['apellido' => 'Benitez']);

        $this->actingAs($this->usuarioDe($a))
            ->get('/socios')
            ->assertOk()
            ->assertSee('Aguirre')
            ->assertDontSee('Benitez');
    }

    public function test_no_se_puede_abrir_ni_modificar_un_socio_de_otra_cooperadora(): void
    {
        $a = $this->cooperadora();
        $b = $this->cooperadora();
        $socioDeB = $this->socioEn($b, ['apellido' => 'Benitez']);

        $presidenteA = $this->usuarioDe($a);

        $this->actingAs($presidenteA)->get("/socios/{$socioDeB->id}/editar")->assertNotFound();

        $this->actingAs($presidenteA)->put("/socios/{$socioDeB->id}", [
            'nombre' => 'Hack', 'apellido' => 'Hack', 'dni' => '11111111',
            'categoria' => 'activo', 'fecha_ingreso' => '2026-01-01',
        ])->assertNotFound();

        $this->actingAs($presidenteA)->patch("/socios/{$socioDeB->id}/baja", ['motivo_baja' => 'x'])->assertNotFound();

        $this->assertTrue(Tenant::como($b, fn () => Socio::find($socioDeB->id))->activo);
    }

    public function test_el_mismo_dni_puede_existir_en_dos_cooperadoras_pero_no_repetirse_en_una(): void
    {
        $a = $this->cooperadora();
        $b = $this->cooperadora();
        $this->socioEn($b, ['dni' => '30111222']);

        $datos = [
            'nombre' => 'Laura', 'apellido' => 'Gomez', 'dni' => '30111222',
            'categoria' => 'activo', 'fecha_ingreso' => '2026-01-01',
        ];

        $presidenteA = $this->usuarioDe($a);

        $this->actingAs($presidenteA)->post('/socios', $datos)->assertSessionHasNoErrors();
        $this->assertSame(1, Tenant::como($a, fn () => Socio::where('dni', '30111222')->count()));

        $this->actingAs($presidenteA)->post('/socios', $datos)->assertSessionHasErrors('dni');
    }

    public function test_no_se_puede_referenciar_un_socio_de_otra_cooperadora_en_tesoreria(): void
    {
        $a = $this->cooperadora();
        $b = $this->cooperadora();
        $socioDeB = $this->socioEn($b);

        $this->actingAs($this->usuarioDe($a))->post('/tesoreria', [
            'fecha' => '2026-05-01',
            'tipo' => 'ingreso',
            'categoria' => 'cuota_social',
            'concepto' => 'Cuota',
            'monto' => 1000,
            'socio_id' => $socioDeB->id,
        ])->assertSessionHasErrors('socio_id');
    }

    public function test_los_registros_nuevos_quedan_asociados_a_la_cooperadora_del_usuario(): void
    {
        $a = $this->cooperadora();

        $this->actingAs($this->usuarioDe($a))->post('/socios', [
            'nombre' => 'Laura', 'apellido' => 'Gomez', 'dni' => '30111222',
            'categoria' => 'activo', 'fecha_ingreso' => '2026-01-01',
        ])->assertSessionHasNoErrors();

        $socio = Tenant::como($a, fn () => Socio::first());
        $this->assertSame($a->id, $socio->establecimiento_id);
    }

    public function test_sin_contexto_de_cooperadora_las_consultas_no_devuelven_nada(): void
    {
        $this->socioEn($this->cooperadora());

        $this->assertSame(0, Socio::count());
    }

    public function test_no_se_puede_crear_un_registro_sin_cooperadora_activa(): void
    {
        $this->expectException(LogicException::class);

        Socio::create([
            'nombre' => 'Ana', 'apellido' => 'Pérez', 'dni' => '1',
            'categoria' => 'activo', 'fecha_ingreso' => '2026-01-01',
        ]);
    }
}
