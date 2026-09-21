<?php

namespace App\Http\Controllers;

use App\Models\Establecimiento;
use App\Services\PanelMigestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Recibe el aviso de cambio de estado que envía MiGestión Panel:
 *   { referencia_externa, email_cliente, estado: activa|vencida|suspendida|cancelada }
 * firmado en el header X-MiGestion-Signature (HMAC SHA-256 del cuerpo crudo
 * con el webhook_secret de este sistema).
 */
class PanelWebhookController extends Controller
{
    public function __invoke(Request $request, PanelMigestion $panel): JsonResponse
    {
        $secreto = (string) config('panel.webhook_secret');

        if ($secreto === '') {
            return response()->json(['error' => 'webhook no configurado'], 503);
        }

        $esperada = hash_hmac('sha256', $request->getContent(), $secreto);

        if (! hash_equals($esperada, (string) $request->header('X-MiGestion-Signature'))) {
            Log::warning('Webhook del panel con firma inválida', ['ip' => $request->ip()]);

            return response()->json(['error' => 'firma inválida'], 401);
        }

        $datos = $request->validate([
            'referencia_externa' => ['required'],
            'estado' => ['required', 'in:activa,vencida,suspendida,cancelada'],
        ]);

        $establecimiento = Establecimiento::find((int) $datos['referencia_externa']);

        if (! $establecimiento) {
            return response()->json(['error' => 'cooperadora no encontrada'], 404);
        }

        $panel->aplicarEstado($establecimiento, $datos['estado']);

        // El aviso no trae la fecha del próximo cobro: se fuerza a que la
        // próxima request la consulte al panel.
        PanelMigestion::olvidarConsulta($establecimiento->id);

        return response()->json(['ok' => true]);
    }
}
