<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Establecimiento extends Model
{
    protected $fillable = [
        'nombre',
        'cue',
        'domicilio',
        'distrito',
        'localidad',
        'provincia',
        'caracter_cuota',
        'monto_cuota',
        'monto_caja_chica_autorizado',
    ];

    protected function casts(): array
    {
        return [
            'monto_cuota' => 'decimal:2',
            'monto_caja_chica_autorizado' => 'decimal:2',
        ];
    }

    /**
     * El sistema administra un solo establecimiento por instalación.
     * Devuelve el registro existente o crea uno vacío para editar.
     */
    public static function actual(): self
    {
        return static::query()->firstOrCreate([], ['nombre' => '']);
    }
}
