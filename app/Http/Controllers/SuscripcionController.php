<?php

namespace App\Http\Controllers;

use App\Models\Establecimiento;
use App\Services\PanelMigestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SuscripcionController extends Controller
{
    /**
     * Lleva al/la Presidente/a al link de pago de Mercado Pago generado por
     * MiGestión Panel para el abono de su cooperadora.
     */
    public function pagar(Request $request, PanelMigestion $panel): RedirectResponse
    {
        $usuario = $request->user();

        abort_unless($usuario->esPresidente() && $usuario->establecimiento_id, 403);

        if (! $panel->habilitado()) {
            return redirect()->away(config('saas.url_pago'));
        }

        $establecimiento = Establecimiento::findOrFail($usuario->establecimiento_id);

        if (! $establecimiento->registradoEnPanel()) {
            $panel->registrarCliente($establecimiento, $usuario);
        }

        $cobro = $panel->generarCobro($establecimiento);

        if ($cobro['link']) {
            return redirect()->away($cobro['link']);
        }

        return back()->with('error', $cobro['error']);
    }
}
