<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tablas cuyos registros pertenecen a una cooperadora.
     * (asamblea_asistentes y asamblea_firmantes heredan el establecimiento
     * de la asamblea y del socio, por eso no llevan la columna.)
     */
    private array $tablas = [
        'socios',
        'miembros_comision',
        'movimientos_tesoreria',
        'asambleas',
        'bienes',
    ];

    public function up(): void
    {
        // 1) Columna nullable + índice, para poder migrar datos existentes.
        foreach ($this->tablas as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->unsignedBigInteger('establecimiento_id')->nullable()->after('id');
                $table->index('establecimiento_id');
            });
        }

        // Los usuarios se asocian a su cooperadora; nullable porque un
        // usuario puede existir antes de que se le asigne una.
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('establecimiento_id')->nullable()->after('id');
            $table->index('establecimiento_id');
        });

        // 2) Backfill: todo lo que ya existía pertenece al primer
        //    establecimiento (la instalación previa tenía uno solo).
        $hayDatos = DB::table('users')->exists()
            || collect($this->tablas)->contains(fn ($t) => DB::table($t)->exists());

        if ($hayDatos) {
            $id = DB::table('establecimientos')->orderBy('id')->value('id')
                ?? DB::table('establecimientos')->insertGetId([
                    'nombre' => 'Establecimiento inicial',
                    'provincia' => 'Buenos Aires',
                    'estado_suscripcion' => 'activa',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            foreach (array_merge($this->tablas, ['users']) as $tabla) {
                DB::table($tabla)->whereNull('establecimiento_id')->update(['establecimiento_id' => $id]);
            }
        }

        // 3) Ahora sí: obligatoria + clave foránea.
        foreach ($this->tablas as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->unsignedBigInteger('establecimiento_id')->nullable(false)->change();
                $table->foreign('establecimiento_id')
                    ->references('id')->on('establecimientos')
                    ->cascadeOnDelete();
            });
        }

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('establecimiento_id')
                ->references('id')->on('establecimientos')
                ->cascadeOnDelete();
        });

        // 4) El DNI de un socio es único dentro de cada cooperadora, no en
        //    todo el sistema (la misma persona puede ser socia en dos).
        Schema::table('socios', function (Blueprint $table) {
            $table->dropUnique(['dni']);
            $table->unique(['establecimiento_id', 'dni']);
        });
    }

    public function down(): void
    {
        Schema::table('socios', function (Blueprint $table) {
            $table->dropUnique(['establecimiento_id', 'dni']);
            $table->unique('dni');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['establecimiento_id']);
            $table->dropIndex(['establecimiento_id']);
            $table->dropColumn('establecimiento_id');
        });

        foreach ($this->tablas as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->dropForeign(['establecimiento_id']);
                $table->dropIndex(['establecimiento_id']);
                $table->dropColumn('establecimiento_id');
            });
        }
    }
};
