<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MovimientoTesoreria extends Model
{
    protected $table = 'movimientos_tesoreria';

    protected $fillable = [
        'fecha',
        'tipo',
        'categoria',
        'concepto',
        'monto',
        'comprobante_numero',
        'socio_id',
        'autorizado_por',
        'registrado_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'monto' => 'decimal:2',
        ];
    }

    /**
     * Categorías de ingresos según el Art. 13° del Estatuto (Art. 24° del
     * Decreto 4767/72).
     */
    public const CATEGORIAS_INGRESO = [
        'cuota_social' => 'Cuota Social',
        'evento_beneficio' => 'Actos, festivales, rifas, bonos, kermeses',
        'donacion_legado' => 'Donaciones y legados',
        'venta_bienes' => 'Venta de bienes muebles dados de baja',
        'quiosco' => 'Quiosco / venta de libros y útiles',
        'subsidio' => 'Subsidios estatales',
        'venta_objetos_alumnos' => 'Venta de objetos fabricados por alumnos/as',
        'otro' => 'Otro',
    ];

    /**
     * Categorías de egresos según los objetivos del Art. 1° del Estatuto.
     */
    public const CATEGORIAS_EGRESO = [
        'comedor_escolar' => 'Comedor escolar',
        'becas' => 'Becas',
        'asistencia_medica' => 'Asistencia médica',
        'turismo_excursiones' => 'Turismo / excursiones educativas',
        'utiles_didacticos' => 'Útiles, libros y elementos didácticos',
        'actividades_extraescolares' => 'Actividades extra-escolares',
        'mantenimiento_edificio' => 'Material y mantenimiento del edificio',
        'gastos_administrativos' => 'Gastos administrativos',
        'otro' => 'Otro',
    ];

    public function socio()
    {
        return $this->belongsTo(Socio::class);
    }

    public function autorizante()
    {
        return $this->belongsTo(MiembroComision::class, 'autorizado_por');
    }

    public function registradoPor()
    {
        return $this->belongsTo(\App\Models\User::class, 'registrado_por');
    }

    public function scopeIngresos(Builder $query): Builder
    {
        return $query->where('tipo', 'ingreso');
    }

    public function scopeEgresos(Builder $query): Builder
    {
        return $query->where('tipo', 'egreso');
    }

    public function scopeEntrePeriodo(Builder $query, ?string $desde, ?string $hasta): Builder
    {
        return $query
            ->when($desde, fn ($q) => $q->where('fecha', '>=', $desde))
            ->when($hasta, fn ($q) => $q->where('fecha', '<=', $hasta));
    }

    public function etiquetaCategoria(): string
    {
        return $this->tipo === 'ingreso'
            ? (self::CATEGORIAS_INGRESO[$this->categoria] ?? $this->categoria)
            : (self::CATEGORIAS_EGRESO[$this->categoria] ?? $this->categoria);
    }
}
