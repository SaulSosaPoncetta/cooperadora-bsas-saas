<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Comisión Directiva (Art. 2° a 11° del Estatuto): Presidente/a,
     * Secretario/a, Tesorero/a, 3 Vocales Titulares y 2 Vocales Suplentes.
     * Se guarda como historial de mandatos: un miembro "vigente" es aquel
     * cuya fecha_fin está vacía.
     */
    public function up(): void
    {
        Schema::create('miembros_comision', function (Blueprint $table) {
            $table->id();
            $table->foreignId('socio_id')->constrained('socios');
            $table->enum('cargo', [
                'presidente',
                'secretario',
                'tesorero',
                'vocal_titular',
                'vocal_suplente',
            ]);
            $table->unsignedTinyInteger('orden')->nullable()
                ->comment('Distingue Vocal Titular/Suplente 1°, 2°, 3°');
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->string('motivo_cese')->nullable();
            $table->timestamps();

            $table->index(['cargo', 'fecha_fin']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('miembros_comision');
    }
};
