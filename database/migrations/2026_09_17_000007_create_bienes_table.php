<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bienes muebles e inmuebles (Art. 16° a 18° del Estatuto;
     * Art. 21° a 23° del Decreto 4767/72).
     */
    public function up(): void
    {
        Schema::create('bienes', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['mueble', 'inmueble']);
            $table->string('descripcion');
            $table->date('fecha_adquisicion');
            $table->decimal('valor_estimado', 12, 2)->nullable();
            $table->enum('origen', ['compra', 'donacion', 'fabricado_alumnos', 'otro'])->default('compra');
            $table->enum('estado', ['en_uso', 'cedido', 'dado_de_baja'])->default('en_uso');

            // Cesión de uso (Art. 16°)
            $table->enum('destino_cesion', ['entidad', 'otra_cooperadora', 'escuela', 'institucion_bien_publico'])->nullable();
            $table->string('cedido_a')->nullable();
            $table->date('fecha_cesion')->nullable();
            $table->boolean('requiere_aprobacion_dgcye')->default(false)
                ->comment('Art. 16° inc. d): cesión a instituciones de bien público por más de 30 días');

            // Baja del patrimonio (Art. 17°: sólo bienes muebles)
            $table->date('fecha_baja')->nullable();
            $table->enum('tipo_baja', ['venta', 'donacion'])->nullable();
            $table->string('motivo_baja')->nullable();
            $table->boolean('aprobacion_baja')->default(false)
                ->comment('Venta: 2/3 de la CD en sesión plenaria. Donación: aprobación de la Dirección Gral. de Cultura y Educación.');

            $table->timestamps();

            $table->index(['tipo', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bienes');
    }
};
