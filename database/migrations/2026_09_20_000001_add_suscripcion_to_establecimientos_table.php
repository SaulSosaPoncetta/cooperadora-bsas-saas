<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Convierte a cada Establecimiento en un cliente (inquilino) del SaaS:
     * agrega los datos de su suscripción.
     *
     * estado_suscripcion: prueba | activa | suspendida | cancelada
     * - prueba: acceso hasta prueba_hasta.
     * - activa: acceso hasta suscripcion_vence_el (null = sin vencimiento).
     */
    public function up(): void
    {
        Schema::table('establecimientos', function (Blueprint $table) {
            $table->string('estado_suscripcion', 20)->default('prueba')->after('provincia');
            $table->string('plan', 50)->nullable()->after('estado_suscripcion');
            $table->date('prueba_hasta')->nullable()->after('plan');
            $table->date('suscripcion_vence_el')->nullable()->after('prueba_hasta');

            $table->index('estado_suscripcion');
        });

        // Los establecimientos que ya existían (instalación previa, un solo
        // cliente) quedan activos y sin vencimiento.
        DB::table('establecimientos')->update(['estado_suscripcion' => 'activa']);
    }

    public function down(): void
    {
        Schema::table('establecimientos', function (Blueprint $table) {
            $table->dropIndex(['estado_suscripcion']);
            $table->dropColumn(['estado_suscripcion', 'plan', 'prueba_hasta', 'suscripcion_vence_el']);
        });
    }
};
