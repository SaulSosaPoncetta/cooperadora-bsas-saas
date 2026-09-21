<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Art. 23° inc. c) del Estatuto: la Asamblea fija el efectivo que
     * puede mantener el/la Tesorero/a para gastos menores y urgentes.
     */
    public function up(): void
    {
        Schema::table('establecimientos', function (Blueprint $table) {
            $table->decimal('monto_caja_chica_autorizado', 10, 2)->default(0)->after('monto_cuota');
        });
    }

    public function down(): void
    {
        Schema::table('establecimientos', function (Blueprint $table) {
            $table->dropColumn('monto_caja_chica_autorizado');
        });
    }
};
