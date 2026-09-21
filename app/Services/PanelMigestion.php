<?php

namespace App\Services;

use App\Models\Establecimiento;
use App\Models\User;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Cliente de MiGestión Panel. Cada cooperadora es un "cliente" del panel y
 * su referencia_externa es el id del establecimiento.
 *
 *   POST /api/registrar-cliente   alta del cliente (y su abono, si hay monto)
 *   GET  /api/estado-cliente      estado: pendiente|activa|vencida|suspendida|cancelada
 *   POST /api/generar-cobro       link de pago de Mercado Pago
 *
 * Toda falla de red se registra en el log y NUNCA rompe al usuario: si el
 * panel no responde, se sigue con el último estado conocido.
 */
class PanelMigestion
{
    public function habilitado(): bool
    {
        return filled(config('panel.url')) && filled(config('panel.api_key'));
    }

    private function http(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('panel.url'), '/'))
            ->withHeaders(['X-Api-Key' => (string) config('panel.api_key')])
            ->acceptJson()
            ->timeout((int) config('panel.timeout', 5));
    }

    /**
     * Da de alta a la cooperadora en el panel. Es idempotente del lado del
     * panel, pero sólo se invoca si todavía no está registrada: el panel
     * identifica al cliente por email, y registrar el mismo establecimiento
     * con otro email crearía un segundo cliente.
     */
    public function registrarCliente(Establecimiento $establecimiento, User $presidente): bool
    {
        if (! $this->habilitado()) {
            return false;
        }

        $datos = [
            'referencia_externa' => (string) $establecimiento->id,
            'nombre' => $establecimiento->nombre ?: 'Cooperadora #'.$establecimiento->id,
            'email' => $presidente->email,
        ];

        if (filled(config('panel.plan')) && filled(config('panel.monto'))) {
            $datos['plan'] = config('panel.plan');
            $datos['monto'] = (float) config('panel.monto');
            $datos['tipo'] = config('panel.tipo', 'recurrente');
        }

        try {
            $this->http()->post('/api/registrar-cliente', $datos)->throw();
        } catch (Throwable $e) {
            Log::warning('No se pudo registrar la cooperadora en el panel', [
                'establecimiento_id' => $establecimiento->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }

        $establecimiento->forceFill(['panel_registrado_at' => now()])->save();

        return true;
    }

    /**
     * Estado de la cooperadora según el panel, o null si no se pudo consultar.
     *
     * @return array<string, mixed>|null
     */
    public function consultarEstado(Establecimiento $establecimiento): ?array
    {
        try {
            $respuesta = $this->http()->get('/api/estado-cliente', [
                'referencia_externa' => (string) $establecimiento->id,
            ]);
        } catch (Throwable $e) {
            Log::warning('No se pudo consultar el estado en el panel', [
                'establecimiento_id' => $establecimiento->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        if ($respuesta->status() === 404) {
            return ['encontrado' => false, 'estado' => 'desconocido'];
        }

        if ($respuesta->failed()) {
            Log::warning('El panel respondió con error al consultar el estado', [
                'establecimiento_id' => $establecimiento->id,
                'status' => $respuesta->status(),
            ]);

            return null;
        }

        return $respuesta->json();
    }

    /**
     * Trae el estado del panel y lo aplica localmente. Devuelve false sólo
     * si el panel no pudo consultarse.
     */
    public function sincronizar(Establecimiento $establecimiento): bool
    {
        $datos = $this->consultarEstado($establecimiento);

        if ($datos === null) {
            return false;
        }

        if ($datos['encontrado'] ?? false) {
            $this->aplicarEstado(
                $establecimiento,
                (string) ($datos['estado'] ?? ''),
                $datos['plan'] ?? null,
                $datos['proxima_fecha_cobro'] ?? null,
            );
        }

        return true;
    }

    /**
     * Consulta al panel como máximo una vez cada `panel.cache_minutos` por
     * cooperadora. Se llama en cada request autenticada; el costo normal es
     * una lectura de caché.
     */
    public function sincronizarSiCorresponde(Establecimiento $establecimiento): void
    {
        if (! $this->habilitado() || ! $establecimiento->registradoEnPanel()) {
            return;
        }

        $clave = self::claveDeCache($establecimiento->id);

        if (! Cache::add($clave, true, now()->addMinutes((int) config('panel.cache_minutos', 60)))) {
            return;
        }

        if (! $this->sincronizar($establecimiento)) {
            // El panel no respondió: se reintenta en un minuto y mientras
            // tanto rige el último estado conocido.
            Cache::put($clave, true, now()->addMinute());
        }
    }

    public static function olvidarConsulta(int $establecimientoId): void
    {
        Cache::forget(self::claveDeCache($establecimientoId));
    }

    private static function claveDeCache(int $establecimientoId): string
    {
        return "panel-sync:{$establecimientoId}";
    }

    /**
     * Traduce el estado del panel al de la suscripción local.
     * "pendiente", "sin_suscripcion" y "desconocido" no cambian nada: rige
     * lo local (por ejemplo, la prueba gratuita en curso).
     */
    public function aplicarEstado(
        Establecimiento $establecimiento,
        string $estadoPanel,
        ?string $plan = null,
        ?string $proximaFechaCobro = null,
    ): void {
        $nuevo = match ($estadoPanel) {
            'activa' => Establecimiento::ESTADO_ACTIVA,
            'vencida' => Establecimiento::ESTADO_VENCIDA,
            'suspendida' => Establecimiento::ESTADO_SUSPENDIDA,
            'cancelada' => Establecimiento::ESTADO_CANCELADA,
            default => null,
        };

        if ($nuevo === null) {
            return;
        }

        // Un intento de pago rechazado durante la prueba no debe cortar una
        // prueba que todavía está vigente.
        if ($nuevo === Establecimiento::ESTADO_VENCIDA
            && $establecimiento->enPrueba()
            && $establecimiento->accesoPermitido()) {
            return;
        }

        $cambios = ['estado_suscripcion' => $nuevo];

        if ($nuevo === Establecimiento::ESTADO_ACTIVA) {
            $cambios['suscripcion_vence_el'] = $proximaFechaCobro
                ? Carbon::parse($proximaFechaCobro)->toDateString()
                : null;

            if (filled($plan)) {
                $cambios['plan'] = $plan;
            }
        }

        $establecimiento->forceFill($cambios)->save();
    }

    /**
     * Link de pago de Mercado Pago para el abono de la cooperadora.
     *
     * @return array{link: ?string, error: ?string}
     */
    public function generarCobro(Establecimiento $establecimiento): array
    {
        try {
            $respuesta = $this->http()->post('/api/generar-cobro', [
                'referencia_externa' => (string) $establecimiento->id,
            ]);
        } catch (Throwable $e) {
            Log::warning('No se pudo generar el cobro en el panel', [
                'establecimiento_id' => $establecimiento->id,
                'error' => $e->getMessage(),
            ]);

            return ['link' => null, 'error' => 'No pudimos comunicarnos con el sistema de pagos. Intentá de nuevo en unos minutos.'];
        }

        $link = $respuesta->successful() ? $respuesta->json('link_pago') : null;

        if ($link) {
            return ['link' => $link, 'error' => null];
        }

        return [
            'link' => null,
            'error' => $respuesta->status() === 404
                ? 'Tu cooperadora todavía no tiene un abono cargado. Contactanos para activarlo.'
                : 'No se pudo generar el link de pago. Intentá de nuevo en unos minutos.',
        ];
    }
}
