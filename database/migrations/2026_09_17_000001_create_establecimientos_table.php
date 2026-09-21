<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Datos del establecimiento educativo al que pertenece la Asociación
     * Cooperadora (encabezado del Estatuto: Establecimiento, Domicilio,
     * Distrito, Localidad) y la configuración de la cuota social (Art. 15°).
     */
    public function up(): void
    {
        Schema::create('establecimientos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('cue')->nullable()->comment('Código Único de Establecimiento');
            $table->string('domicilio')->nullable();
            $table->string('distrito')->nullable();
            $table->string('localidad')->nullable();
            $table->string('provincia')->default('Buenos Aires');
            $table->enum('caracter_cuota', ['mensual', 'anual'])->default('mensual');
            $table->decimal('monto_cuota', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('establecimientos');
    }
};
