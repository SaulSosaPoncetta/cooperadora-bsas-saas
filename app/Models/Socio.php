<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Socio extends Model
{
    protected $fillable = [
        'nombre',
        'apellido',
        'dni',
        'domicilio',
        'telefono',
        'email',
        'categoria',
        'fecha_ingreso',
        'activo',
        'fecha_baja',
        'motivo_baja',
        'cuotas_adeudadas',
    ];

    protected function casts(): array
    {
        return [
            'fecha_ingreso' => 'date',
            'fecha_baja' => 'date',
            'activo' => 'boolean',
        ];
    }

    public function nombreCompleto(): string
    {
        return "{$this->apellido}, {$this->nombre}";
    }

    /**
     * Art. 26° del Estatuto: sólo los/as Socios/as Activos/as con una
     * antigüedad mínima de 30 días tienen derecho a voz y voto.
     */
    public function tieneDerechoAVoto(): bool
    {
        return $this->activo
            && $this->categoria === 'activo'
            && $this->fecha_ingreso->diffInDays(now()) >= 30;
    }

    /**
     * Art. 22° del Estatuto: deja de ser socio/a quien adeude más de
     * 3 cuotas sociales.
     */
    public function superaLimiteDeuda(): bool
    {
        return $this->cuotas_adeudadas > 3;
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function scopeCategoria(Builder $query, string $categoria): Builder
    {
        return $query->where('categoria', $categoria);
    }
}
