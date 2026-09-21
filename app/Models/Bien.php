<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bien extends Model
{
    protected $table = 'bienes';

    protected $fillable = [
        'tipo',
        'descripcion',
        'fecha_adquisicion',
        'valor_estimado',
        'origen',
        'estado',
        'destino_cesion',
        'cedido_a',
        'fecha_cesion',
        'requiere_aprobacion_dgcye',
        'fecha_baja',
        'tipo_baja',
        'motivo_baja',
        'aprobacion_baja',
    ];

    protected function casts(): array
    {
        return [
            'fecha_adquisicion' => 'date',
            'fecha_cesion' => 'date',
            'fecha_baja' => 'date',
            'valor_estimado' => 'decimal:2',
            'requiere_aprobacion_dgcye' => 'boolean',
            'aprobacion_baja' => 'boolean',
        ];
    }

    public const ORIGENES = [
        'compra' => 'Compra',
        'donacion' => 'Donación',
        'fabricado_alumnos' => 'Fabricado por alumnos/as',
        'otro' => 'Otro',
    ];

    public const DESTINOS_CESION = [
        'entidad' => 'Uso interno de la Entidad',
        'otra_cooperadora' => 'Otra Asociación Cooperadora',
        'escuela' => 'La Escuela (necesidad imperiosa, a petición del/la Asesor/a)',
        'institucion_bien_publico' => 'Institución de bien público',
    ];

    /**
     * Sólo los bienes muebles pueden egresar del patrimonio de la
     * Entidad (Art. 17°). Los inmuebles ingresan al patrimonio fiscal
     * (Art. 18°) y no son de libre disposición de la Cooperadora.
     */
    public function puedeDarseDeBaja(): bool
    {
        return $this->tipo === 'mueble' && $this->estado !== 'dado_de_baja';
    }
}
