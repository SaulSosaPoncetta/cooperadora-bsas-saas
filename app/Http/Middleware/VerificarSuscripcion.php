<?php

namespace App\Http\Middleware;

use App\Models\Establecimiento;
use App\Services\PanelMigestion;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bloquea el acceso de las cooperadoras cuya suscripción está vencida,
 * suspendida o cancelada, y comparte con las vistas el aviso de vencimiento.
 */
class VerificarSuscripcion
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if (! $usuario) {
            return $next($request);
        }

        $establecimiento = $usuario->establecimiento_id
            ? Establecimiento::find($usuario->establecimiento_id)
            : null;

        if (! $establecimiento) {
            return response()->view('suscripcion.inactiva', ['establecimiento' => null], 403);
        }

        // Red de seguridad por si se perdió un aviso del panel: consulta el
        // estado cada tanto (ver config/panel.php) y actualiza el local.
        app(PanelMigestion::class)->sincronizarSiCorresponde($establecimiento);

        if (! $establecimiento->accesoPermitido()) {
            return response()->view('suscripcion.inactiva', ['establecimiento' => $establecimiento], 402);
        }

        view()->share('establecimientoActual', $establecimiento);
        view()->share('suscripcionAviso', $establecimiento->avisoSuscripcion());

        return $next($request);
    }
}
