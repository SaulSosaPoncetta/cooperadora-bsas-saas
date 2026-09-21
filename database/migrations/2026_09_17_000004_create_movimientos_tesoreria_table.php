<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Libro de Tesorería (Art. 9° y 13° a 15° del Estatuto; Art. 24° y 25°
     * del Decreto 4767/72). Ingresos y egresos con comprobante, y el/la
     * miembro de Comisión que autoriza la firma conjunta (Art. 14°).
     */
    public function up(): void
    {
        Schema::create('movimientos_tesoreria', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->enum('tipo', ['ingreso', 'egreso']);
            $table->string('categoria');
            $table->string('concepto');
            $table->decimal('monto', 12, 2);
            $table->string('comprobante_numero')->nullable();
            $table->foreignId('socio_id')->nullable()->constrained('socios')->nullOnDelete();
            $table->foreignId('autorizado_por')->nullable()->constrained('miembros_comision')->nullOnDelete();
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tipo', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_tesoreria');
    }
};
