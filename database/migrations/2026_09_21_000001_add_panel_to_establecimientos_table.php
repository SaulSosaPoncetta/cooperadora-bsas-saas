<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Marca cuándo se dio de alta la cooperadora en MiGestión Panel. La
     * referencia_externa que usa el panel es el id del establecimiento.
     */
    public function up(): void
    {
        Schema::table('establecimientos', function (Blueprint $table) {
            $table->timestamp('panel_registrado_at')->nullable()->after('suscripcion_vence_el');
        });
    }

    public function down(): void
    {
        Schema::table('establecimientos', function (Blueprint $table) {
            $table->dropColumn('panel_registrado_at');
        });
    }
};
