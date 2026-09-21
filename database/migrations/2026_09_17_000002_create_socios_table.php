<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Registro de Socios/as (Art. 19° a 22° del Estatuto).
     * Categorías: Activos/as, Honorarios/as, Adherentes.
     */
    public function up(): void
    {
        Schema::create('socios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('apellido');
            $table->string('dni', 20)->unique();
            $table->string('domicilio')->nullable();
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();
            $table->enum('categoria', ['activo', 'honorario', 'adherente'])->default('activo');
            $table->date('fecha_ingreso');
            $table->boolean('activo')->default(true);
            $table->date('fecha_baja')->nullable();
            $table->string('motivo_baja')->nullable();
            $table->unsignedSmallInteger('cuotas_adeudadas')->default(0);
            $table->timestamps();

            $table->index(['categoria', 'activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('socios');
    }
};
