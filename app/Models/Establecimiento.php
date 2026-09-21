<?php

namespace App\Models;

use App\Support\Tenant;
use Database\Factories\EstablecimientoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Cada Establecimiento es una Asociación Cooperadora cliente del SaaS
 * (el "inquilino"). Sus datos de suscripción los administra el sistema
 * (alta/registro) o tu panel de gestión, nunca el formulario del usuario.
 */
class Establecimiento extends Model
{
    use HasFactory;

    public const ESTADO_PRUEBA = 'prueba';

    public const ESTADO_ACTIVA = 'activa';

    public const ESTADO_VENCIDA = 'vencida';

    public const ESTADO_SUSPENDIDA = 'suspendida';

    public const ESTADO_CANCELADA = 'cancelada';

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
            'prueba_hasta' => 'date',
            'suscripcion_vence_el' => 'date',
            'panel_registrado_at' => 'datetime',
        ];
    }

    protected static function newFactory(): EstablecimientoFactory
    {
        return EstablecimientoFactory::new();
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Establecimiento de la sesión actual (el del usuario autenticado).
     */
    public static function actual(): self
    {
        $id = Tenant::id();

        abort_unless($id, 403, 'Tu usuario no está asociado a ninguna cooperadora.');

        return static::findOrFail($id);
    }

    /*
    |--------------------------------------------------------------------------
    | Suscripción
    |--------------------------------------------------------------------------
    | prueba     -> acceso hasta prueba_hasta.
    | activa     -> acceso hasta suscripcion_vence_el (+ días de gracia).
    |               Sin fecha de vencimiento = sin límite.
    | vencida / suspendida / cancelada -> sin acceso (los fija el panel al
    |               registrar un pago rechazado, una pausa o una baja).
    */

    public function registradoEnPanel(): bool
    {
        return $this->panel_registrado_at !== null;
    }

    public function enPrueba(): bool
    {
        return $this->estado_suscripcion === self::ESTADO_PRUEBA;
    }

    /**
     * Fecha a partir de la cual el acceso se bloquea, o null si no vence.
     */
    private function limiteDeAcceso(): ?Carbon
    {
        $fecha = match ($this->estado_suscripcion) {
            self::ESTADO_PRUEBA => $this->prueba_hasta,
            self::ESTADO_ACTIVA => $this->suscripcion_vence_el,
            default => null,
        };

        if (! $fecha) {
            return null;
        }

        $limite = $fecha->copy()->endOfDay();

        if ($this->estado_suscripcion === self::ESTADO_ACTIVA) {
            $limite->addDays((int) config('saas.dias_gracia'));
        }

        return $limite;
    }

    public function accesoPermitido(): bool
    {
        if (! in_array($this->estado_suscripcion, [self::ESTADO_PRUEBA, self::ESTADO_ACTIVA], true)) {
            return false;
        }

        $limite = $this->limiteDeAcceso();

        return $limite === null || $limite->isFuture();
    }

    /**
     * Días hasta la fecha de vencimiento (negativo si ya venció).
     */
    public function diasParaVencer(): ?int
    {
        $fecha = $this->enPrueba() ? $this->prueba_hasta : $this->suscripcion_vence_el;

        if (! $fecha) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($fecha->copy()->startOfDay(), false);
    }

    /**
     * Aviso para mostrar en la parte superior de la app, o null si no hace
     * falta avisar nada.
     *
     * @return array{tipo: string, mensaje: string}|null
     */
    public function avisoSuscripcion(): ?array
    {
        $dias = $this->diasParaVencer();

        if ($dias === null) {
            return null;
        }

        if ($this->enPrueba()) {
            return [
                'tipo' => $dias <= 5 ? 'warning' : 'info',
                'mensaje' => $dias === 0
                    ? 'Tu período de prueba termina hoy.'
                    : "Estás en período de prueba: te quedan {$dias} día(s).",
            ];
        }

        if ($dias < 0) {
            $bloqueo = $this->suscripcion_vence_el->copy()
                ->addDays((int) config('saas.dias_gracia'))
                ->format('d/m/Y');

            return [
                'tipo' => 'danger',
                'mensaje' => 'Tu abono venció el '.$this->suscripcion_vence_el->format('d/m/Y')
                    ."; el acceso se suspenderá el {$bloqueo} si no se registra el pago.",
            ];
        }

        if ($dias <= 7) {
            return [
                'tipo' => 'warning',
                'mensaje' => $dias === 0
                    ? 'Tu abono vence hoy.'
                    : "Tu abono vence en {$dias} día(s) ({$this->suscripcion_vence_el->format('d/m/Y')}).",
            ];
        }

        return null;
    }
}
