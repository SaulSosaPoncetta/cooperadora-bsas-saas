<?php

namespace Tests\Feature\Tenancy;

use App\Models\Establecimiento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\CreaCooperadoras;
use Tests\TestCase;

class IntegracionPanelTest extends TestCase
{
    use CreaCooperadoras;
    use RefreshDatabase;

    private function activarPanel(): void
    {
        config([
            'panel.url' => 'https://panel.test',
            'panel.api_key' => 'clave-api',
            'panel.webhook_secret' => 'secreto-webhook',
            'panel.plan' => 'Mensual',
            'panel.monto' => 15000,
            'panel.tipo' => 'recurrente',
            'panel.cache_minutos' => 60,
        ]);
        Cache::flush();
    }

    private function webhook(array $payload, ?string $secreto = 'secreto-webhook')
    {
        $cuerpo = json_encode($payload);
        $firma = hash_hmac('sha256', $cuerpo, $secreto ?? 'otro');

        return $this->call('POST', '/webhooks/panel', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_MIGESTION_SIGNATURE' => $firma,
        ], $cuerpo);
    }

    private function datosRegistro(): array
    {
        return [
            'establecimiento_nombre' => 'Escuela N° 12',
            'name' => 'Paula Presidenta',
            'email' => 'paula@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];
    }

    /* ---------------------------------------------------------- alta */

    public function test_al_registrarse_la_cooperadora_se_da_de_alta_en_el_panel(): void
    {
        $this->activarPanel();
        Http::fake(['panel.test/api/registrar-cliente' => Http::response(['ok' => true], 201)]);

        $this->post('/register', $this->datosRegistro())->assertRedirect();

        $cooperadora = Establecimiento::firstOrFail();

        Http::assertSent(function (HttpRequest $request) use ($cooperadora) {
            return $request->url() === 'https://panel.test/api/registrar-cliente'
                && $request->hasHeader('X-Api-Key', 'clave-api')
                && $request['referencia_externa'] === (string) $cooperadora->id
                && $request['email'] === 'paula@example.com'
                && $request['nombre'] === 'Escuela N° 12'
                && $request['plan'] === 'Mensual'
                && (float) $request['monto'] === 15000.0
                && $request['tipo'] === 'recurrente';
        });
        $this->assertTrue($cooperadora->registradoEnPanel());
    }

    public function test_si_el_panel_no_responde_el_registro_igual_se_completa(): void
    {
        $this->activarPanel();
        Http::fake(['panel.test/*' => Http::response(['error' => 'x'], 500)]);

        $this->post('/register', $this->datosRegistro())->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
        $this->assertFalse(Establecimiento::firstOrFail()->registradoEnPanel());
    }

    public function test_sin_monto_configurado_se_registra_el_cliente_sin_abono(): void
    {
        $this->activarPanel();
        config(['panel.monto' => null]);
        Http::fake(['panel.test/*' => Http::response(['ok' => true], 201)]);

        $this->post('/register', $this->datosRegistro());

        Http::assertSent(fn (HttpRequest $r) => ! isset($r['monto']) && ! isset($r['plan']));
    }

    public function test_con_la_integracion_apagada_no_se_llama_al_panel(): void
    {
        Http::fake();

        $this->post('/register', $this->datosRegistro())->assertRedirect();

        Http::assertNothingSent();
    }

    /* ------------------------------------------------------- webhook */

    public function test_el_webhook_suspende_la_cooperadora_y_bloquea_el_acceso(): void
    {
        $this->activarPanel();
        $cooperadora = $this->cooperadora();
        $presidente = $this->usuarioDe($cooperadora);

        $this->webhook([
            'referencia_externa' => (string) $cooperadora->id,
            'email_cliente' => $presidente->email,
            'estado' => 'suspendida',
        ])->assertOk();

        $this->assertSame('suspendida', $cooperadora->fresh()->estado_suscripcion);
        $this->actingAs($presidente)->get('/profile')->assertStatus(402);
    }

    public function test_el_webhook_activa_una_cooperadora_vencida_y_le_devuelve_el_acceso(): void
    {
        $this->activarPanel();
        Http::fake(['panel.test/api/estado-cliente*' => Http::response([
            'encontrado' => true, 'estado' => 'activa', 'plan' => 'Mensual',
            'proxima_fecha_cobro' => now()->addDays(30)->toDateString().'T00:00:00.000000Z',
        ])]);
        $cooperadora = Establecimiento::factory()->suspendida()->create(['panel_registrado_at' => now()]);
        $presidente = $this->usuarioDe($cooperadora);

        $this->webhook(['referencia_externa' => (string) $cooperadora->id, 'estado' => 'activa'])->assertOk();

        $this->actingAs($presidente)->get('/profile')->assertOk();

        $cooperadora->refresh();
        $this->assertSame('activa', $cooperadora->estado_suscripcion);
        $this->assertSame(now()->addDays(30)->toDateString(), $cooperadora->suscripcion_vence_el->toDateString());
        $this->assertSame('Mensual', $cooperadora->plan);
    }

    public function test_el_webhook_con_firma_invalida_se_rechaza_y_no_cambia_nada(): void
    {
        $this->activarPanel();
        $cooperadora = $this->cooperadora();

        $this->webhook(['referencia_externa' => (string) $cooperadora->id, 'estado' => 'cancelada'], 'clave-falsa')
            ->assertUnauthorized();

        $this->assertSame('activa', $cooperadora->fresh()->estado_suscripcion);
    }

    public function test_el_webhook_sin_secreto_configurado_no_acepta_nada(): void
    {
        $cooperadora = $this->cooperadora();
        config(['panel.webhook_secret' => null]);

        $this->webhook(['referencia_externa' => (string) $cooperadora->id, 'estado' => 'cancelada'])
            ->assertStatus(503);

        $this->assertSame('activa', $cooperadora->fresh()->estado_suscripcion);
    }

    public function test_el_webhook_de_una_cooperadora_inexistente_responde_404(): void
    {
        $this->activarPanel();

        $this->webhook(['referencia_externa' => '99999', 'estado' => 'activa'])->assertNotFound();
    }

    public function test_un_pago_rechazado_no_corta_una_prueba_vigente(): void
    {
        $this->activarPanel();
        $cooperadora = Establecimiento::factory()->enPrueba(10)->create();

        $this->webhook(['referencia_externa' => (string) $cooperadora->id, 'estado' => 'vencida'])->assertOk();

        $this->assertSame('prueba', $cooperadora->fresh()->estado_suscripcion);
    }

    public function test_un_pago_rechazado_vence_una_cuenta_activa(): void
    {
        $this->activarPanel();
        $cooperadora = $this->cooperadora();

        $this->webhook(['referencia_externa' => (string) $cooperadora->id, 'estado' => 'vencida'])->assertOk();

        $this->assertSame('vencida', $cooperadora->fresh()->estado_suscripcion);
        $this->actingAs($this->usuarioDe($cooperadora))->get('/profile')->assertStatus(402);
    }

    /* ------------------------------------------------ sincronización */

    public function test_una_prueba_terminada_pasa_a_activa_si_el_panel_dice_que_pago(): void
    {
        $this->activarPanel();
        Http::fake(['panel.test/api/estado-cliente*' => Http::response([
            'encontrado' => true, 'estado' => 'activa', 'plan' => 'Mensual',
            'proxima_fecha_cobro' => now()->addDays(20)->toDateString().'T00:00:00.000000Z',
        ])]);
        $cooperadora = Establecimiento::factory()->enPrueba(-3)->create(['panel_registrado_at' => now()]);

        $this->actingAs($this->usuarioDe($cooperadora))->get('/profile')->assertOk();

        $this->assertSame('activa', $cooperadora->fresh()->estado_suscripcion);
        Http::assertSent(fn (HttpRequest $r) => str_contains($r->url(), '/api/estado-cliente')
            && $r['referencia_externa'] === (string) $cooperadora->id
            && $r->hasHeader('X-Api-Key', 'clave-api'));
    }

    public function test_el_estado_se_consulta_una_sola_vez_por_ventana_de_cache(): void
    {
        $this->activarPanel();
        Http::fake(['panel.test/api/estado-cliente*' => Http::response([
            'encontrado' => true, 'estado' => 'activa', 'plan' => 'Mensual', 'proxima_fecha_cobro' => null,
        ])]);
        $cooperadora = Establecimiento::factory()->create(['panel_registrado_at' => now()]);
        $presidente = $this->usuarioDe($cooperadora);

        $this->actingAs($presidente)->get('/profile')->assertOk();
        $this->actingAs($presidente)->get('/profile')->assertOk();
        $this->actingAs($presidente)->get('/profile')->assertOk();

        Http::assertSentCount(1);
    }

    public function test_si_el_panel_dice_vencida_el_acceso_se_bloquea(): void
    {
        $this->activarPanel();
        Http::fake(['panel.test/api/estado-cliente*' => Http::response([
            'encontrado' => true, 'estado' => 'vencida', 'plan' => 'Mensual', 'proxima_fecha_cobro' => null,
        ])]);
        $cooperadora = Establecimiento::factory()->create(['panel_registrado_at' => now()]);

        $this->actingAs($this->usuarioDe($cooperadora))->get('/profile')->assertStatus(402);
    }

    public function test_si_el_panel_esta_caido_rige_el_ultimo_estado_conocido(): void
    {
        $this->activarPanel();
        Http::fake(fn () => throw new ConnectionException('sin conexión'));
        $cooperadora = Establecimiento::factory()->create(['panel_registrado_at' => now()]);

        $this->actingAs($this->usuarioDe($cooperadora))->get('/profile')->assertOk();
    }

    public function test_un_estado_pendiente_del_panel_no_pisa_la_prueba_local(): void
    {
        $this->activarPanel();
        Http::fake(['panel.test/api/estado-cliente*' => Http::response([
            'encontrado' => true, 'estado' => 'pendiente', 'plan' => 'Mensual', 'proxima_fecha_cobro' => null,
        ])]);
        $cooperadora = Establecimiento::factory()->enPrueba(10)->create(['panel_registrado_at' => now()]);

        $this->actingAs($this->usuarioDe($cooperadora))->get('/profile')->assertOk();

        $this->assertSame('prueba', $cooperadora->fresh()->estado_suscripcion);
    }

    public function test_las_cooperadoras_no_registradas_en_el_panel_no_se_consultan(): void
    {
        $this->activarPanel();
        Http::fake();

        $this->actingAs($this->usuarioDe($this->cooperadora()))->get('/profile')->assertOk();

        Http::assertNothingSent();
    }

    /* ------------------------------------------------------- pago */

    public function test_el_presidente_de_una_cuenta_bloqueada_es_llevado_al_link_de_pago(): void
    {
        $this->activarPanel();
        Http::fake([
            'panel.test/api/generar-cobro' => Http::response(['link_pago' => 'https://www.mercadopago.com.ar/checkout/abc']),
            'panel.test/api/estado-cliente*' => Http::response(['encontrado' => true, 'estado' => 'vencida']),
        ]);
        $cooperadora = Establecimiento::factory()->vencida(30)->create(['panel_registrado_at' => now()]);
        $presidente = $this->usuarioDe($cooperadora);

        $this->actingAs($presidente)->get('/profile')->assertStatus(402);

        $this->actingAs($presidente)->post('/suscripcion/pagar')
            ->assertRedirect('https://www.mercadopago.com.ar/checkout/abc');

        Http::assertSent(fn (HttpRequest $r) => str_contains($r->url(), '/api/generar-cobro')
            && $r['referencia_externa'] === (string) $cooperadora->id);
    }

    public function test_solo_el_presidente_puede_pagar_el_abono(): void
    {
        $this->activarPanel();
        Http::fake();

        $this->actingAs($this->usuarioDe($this->cooperadora(), 'Tesorero'))
            ->post('/suscripcion/pagar')
            ->assertForbidden();

        Http::assertNothingSent();
    }

    public function test_si_no_hay_abono_cargado_se_avisa_sin_romper(): void
    {
        $this->activarPanel();
        Http::fake(['panel.test/api/generar-cobro' => Http::response(['error' => 'no tiene una suscripción cargada'], 404)]);
        $cooperadora = Establecimiento::factory()->create(['panel_registrado_at' => now()]);

        $this->actingAs($this->usuarioDe($cooperadora))->post('/suscripcion/pagar')
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_con_el_panel_apagado_el_pago_redirige_a_la_url_configurada(): void
    {
        config(['saas.url_pago' => 'https://www.migestion.com.ar']);

        $this->actingAs($this->usuarioDe($this->cooperadora()))->post('/suscripcion/pagar')
            ->assertRedirect('https://www.migestion.com.ar');
    }

    /* ---------------------------------------------------- comando */

    public function test_el_comando_registra_las_cooperadoras_que_faltan(): void
    {
        $this->activarPanel();
        Http::fake(['panel.test/*' => Http::response(['ok' => true], 201)]);
        $cooperadora = $this->cooperadora();
        $presidente = $this->usuarioDe($cooperadora);

        $this->artisan('panel:sincronizar', ['--registrar' => true])->assertSuccessful();

        Http::assertSent(fn (HttpRequest $r) => str_contains($r->url(), '/api/registrar-cliente')
            && $r['email'] === $presidente->email);
        $this->assertTrue($cooperadora->fresh()->registradoEnPanel());
    }

    public function test_el_comando_avisa_si_la_integracion_esta_apagada(): void
    {
        $this->artisan('panel:sincronizar')->assertFailed();
    }
}
