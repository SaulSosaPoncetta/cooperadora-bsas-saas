<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Asambleas Ordinarias y Extraordinarias (Art. 23° a 41° del Estatuto;
     * Art. 9° a 12° y 32° a 35° del Decreto 4767/72).
     */
    public function up(): void
    {
        Schema::create('asambleas', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['ordinaria', 'extraordinaria']);
            $table->string('motivo')->nullable()
                ->comment('Causal de convocatoria si es Extraordinaria (Art. 24°)');
            $table->date('fecha_convocatoria');
            $table->date('fecha');
            $table->time('hora')->nullable();
            $table->text('orden_del_dia');
            $table->enum('estado', ['convocada', 'realizada', 'anulada'])->default('convocada');

            // Datos que se completan al realizarse la Asamblea (Art. 30°, 36°)
            $table->unsignedInteger('socios_activos_habilitados')->nullable()
                ->comment('Padrón de socios/as con derecho a voto al momento de la Asamblea (Art. 39°)');
            $table->foreignId('presidente_asamblea_id')->nullable()->constrained('socios')->nullOnDelete();
            $table->foreignId('secretario_actas_id')->nullable()->constrained('socios')->nullOnDelete();
            $table->text('resumen_acta')->nullable();
            $table->date('fecha_elevada_direccion')->nullable()
                ->comment('Cumplimiento del Art. 44° del Estatuto: copia del acta a la Dirección de Cooperación Escolar dentro de los 15 días');
            $table->string('motivo_anulacion')->nullable();

            $table->timestamps();
        });

        Schema::create('asamblea_asistentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asamblea_id')->constrained('asambleas')->cascadeOnDelete();
            $table->foreignId('socio_id')->constrained('socios')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['asamblea_id', 'socio_id']);
        });

        Schema::create('asamblea_firmantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asamblea_id')->constrained('asambleas')->cascadeOnDelete();
            $table->foreignId('socio_id')->constrained('socios')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['asamblea_id', 'socio_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asamblea_firmantes');
        Schema::dropIfExists('asamblea_asistentes');
        Schema::dropIfExists('asambleas');
    }
};
