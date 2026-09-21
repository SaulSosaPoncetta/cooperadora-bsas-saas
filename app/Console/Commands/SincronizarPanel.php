<?php

namespace App\Console\Commands;

use App\Models\Establecimiento;
use App\Models\User;
use App\Services\PanelMigestion;
use Illuminate\Console\Command;

class SincronizarPanel extends Command
{
    protected $signature = 'panel:sincronizar {--registrar : También da de alta en el panel a las cooperadoras que todavía no están}';

    protected $description = 'Sincroniza con MiGestión Panel el estado de suscripción de las cooperadoras';

    public function handle(PanelMigestion $panel): int
    {
        if (! $panel->habilitado()) {
            $this->warn('La integración con el panel está apagada (faltan PANEL_URL o PANEL_API_KEY).');

            return self::FAILURE;
        }

        $sincronizadas = 0;
        $fallidas = 0;

        Establecimiento::whereNotNull('panel_registrado_at')->each(function (Establecimiento $e) use ($panel, &$sincronizadas, &$fallidas) {
            $panel->sincronizar($e) ? $sincronizadas++ : $fallidas++;
        });

        $this->info("Estado sincronizado: {$sincronizadas} cooperadora(s), {$fallidas} con error.");

        if ($this->option('registrar')) {
            $registradas = 0;
            $sinPresidente = 0;

            Establecimiento::whereNull('panel_registrado_at')->each(function (Establecimiento $e) use ($panel, &$registradas, &$sinPresidente) {
                $presidente = User::where('establecimiento_id', $e->id)->role('Presidente')->orderBy('id')->first();

                if (! $presidente) {
                    $sinPresidente++;

                    return;
                }

                if ($panel->registrarCliente($e, $presidente)) {
                    $registradas++;
                }
            });

            $this->info("Dadas de alta en el panel: {$registradas}. Sin Presidente/a (omitidas): {$sinPresidente}.");
        }

        return self::SUCCESS;
    }
}
