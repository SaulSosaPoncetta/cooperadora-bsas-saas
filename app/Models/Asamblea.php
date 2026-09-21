<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asamblea extends Model
{
    protected $fillable = [
        'tipo',
        'motivo',
        'fecha_convocatoria',
        'fecha',
        'hora',
        'orden_del_dia',
        'estado',
        'socios_activos_habilitados',
        'presidente_asamblea_id',
        'secretario_actas_id',
        'resumen_acta',
        'memoria',
        'fecha_elevada_direccion',
        'motivo_anulacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_convocatoria' => 'date',
            'fecha' => 'date',
            'fecha_elevada_direccion' => 'date',
        ];
    }

    public function asistentes()
    {
        return $this->belongsToMany(Socio::class, 'asamblea_asistentes')->withTimestamps();
    }

    public function firmantes()
    {
        return $this->belongsToMany(Socio::class, 'asamblea_firmantes')->withTimestamps();
    }

    public function presidenteAsamblea()
    {
        return $this->belongsTo(Socio::class, 'presidente_asamblea_id');
    }

    public function secretarioActas()
    {
        return $this->belongsTo(Socio::class, 'secretario_actas_id');
    }

    /**
     * Art. 27°: la Asamblea Ordinaria se convoca con 30 días corridos
     * de anticipación. Art. 24°: la Extraordinaria, entre 5 y 15 días.
     */
    public function cumpleAnticipacionConvocatoria(): bool
    {
        $dias = $this->fecha_convocatoria->diffInDays($this->fecha);

        return $this->tipo === 'ordinaria'
            ? $dias >= 30
            : ($dias >= 5 && $dias <= 15);
    }

    /**
     * Art. 30°: quórum de 50% de socios/as activos/as con derecho a voto;
     * pasada una hora, alcanza con igualar el número de miembros de la
     * Comisión Directiva (Art. 3°: 8 titulares).
     */
    public function cumpleQuorumInicial(): bool
    {
        if (! $this->socios_activos_habilitados) {
            return false;
        }

        return $this->asistentes()->count() >= ceil($this->socios_activos_habilitados / 2);
    }

    public function cumpleQuorumPasadaUnaHora(): bool
    {
        return $this->asistentes()->count() >= 8; // Presidente/a, Secretario/a, Tesorero/a y 5 vocales
    }

    /**
     * Art. 44° del Estatuto: dentro de los 15 días de realizada la
     * Asamblea debe elevarse copia del acta a la Dirección de Cooperación
     * Escolar.
     */
    public function rendicionVencida(): bool
    {
        return $this->estado === 'realizada'
            && ! $this->fecha_elevada_direccion
            && $this->fecha->diffInDays(now()) > 15;
    }

    public function diasRestantesRendicion(): ?int
    {
        if ($this->estado !== 'realizada' || $this->fecha_elevada_direccion) {
            return null;
        }

        return 15 - $this->fecha->diffInDays(now());
    }
}
