<?php

namespace App\Models\Concerns;

use App\Models\Establecimiento;
use App\Models\Scopes\EstablecimientoScope;
use App\Support\Tenant;
use LogicException;

/**
 * Marca un modelo como propiedad de una cooperadora: aplica el filtro
 * automático por establecimiento y completa establecimiento_id al crear.
 */
trait PerteneceAEstablecimiento
{
    protected static function bootPerteneceAEstablecimiento(): void
    {
        static::addGlobalScope(new EstablecimientoScope);

        static::creating(function ($modelo) {
            if (! empty($modelo->establecimiento_id)) {
                return;
            }

            $id = Tenant::id();

            if ($id === null) {
                throw new LogicException(
                    'No hay un establecimiento activo para asignar a '.static::class.'.'
                );
            }

            $modelo->establecimiento_id = $id;
        });
    }

    public function establecimiento()
    {
        return $this->belongsTo(Establecimiento::class);
    }
}
