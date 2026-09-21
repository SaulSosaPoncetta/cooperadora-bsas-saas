<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAEstablecimiento;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MiembroComision extends Model
{
    use PerteneceAEstablecimiento;

    protected $table = 'miembros_comision';

    protected $fillable = [
        'socio_id',
        'cargo',
        'orden',
        'fecha_inicio',
        'fecha_fin',
        'motivo_cese',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
        ];
    }

    /**
     * Cupo máximo por cargo, según el Art. 3° del Estatuto.
     */
    public const CUPOS = [
        'presidente' => 1,
        'secretario' => 1,
        'tesorero' => 1,
        'vocal_titular' => 3,
        'vocal_suplente' => 2,
    ];

    public const ETIQUETAS_CARGO = [
        'presidente' => 'Presidente/a',
        'secretario' => 'Secretario/a',
        'tesorero' => 'Tesorero/a',
        'vocal_titular' => 'Vocal Titular',
        'vocal_suplente' => 'Vocal Suplente',
    ];

    public function socio()
    {
        return $this->belongsTo(Socio::class);
    }

    public function scopeVigentes(Builder $query): Builder
    {
        return $query->whereNull('fecha_fin');
    }

    public function vigente(): bool
    {
        return $this->fecha_fin === null;
    }

    public function etiquetaCargo(): string
    {
        $etiqueta = self::ETIQUETAS_CARGO[$this->cargo] ?? $this->cargo;

        return $this->orden ? "{$etiqueta} {$this->orden}°" : $etiqueta;
    }

    /**
     * Cuántos lugares vigentes quedan libres para un cargo dado
     * (Art. 3°: 1 Presidente/a, 1 Secretario/a, 1 Tesorero/a,
     * 3 Vocales Titulares, 2 Vocales Suplentes).
     */
    public static function cupoDisponible(string $cargo): int
    {
        $ocupados = static::vigentes()->where('cargo', $cargo)->count();

        return max(0, (self::CUPOS[$cargo] ?? 0) - $ocupados);
    }

    /**
     * Próximo número de orden libre para Vocales Titulares/Suplentes.
     */
    public static function proximoOrden(string $cargo): ?int
    {
        if (! in_array($cargo, ['vocal_titular', 'vocal_suplente'])) {
            return null;
        }

        $ocupados = static::vigentes()->where('cargo', $cargo)->pluck('orden')->all();

        for ($i = 1; $i <= self::CUPOS[$cargo]; $i++) {
            if (! in_array($i, $ocupados)) {
                return $i;
            }
        }

        return null;
    }
}
