<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Art. 23° inc. a) y 44° del Estatuto: la Asamblea Ordinaria aprueba
     * la Memoria del ejercicio, que se eleva junto al Balance.
     */
    public function up(): void
    {
        Schema::table('asambleas', function (Blueprint $table) {
            $table->text('memoria')->nullable()->after('resumen_acta');
        });
    }

    public function down(): void
    {
        Schema::table('asambleas', function (Blueprint $table) {
            $table->dropColumn('memoria');
        });
    }
};
