<?php

namespace App\Models\Scopes;

use App\Support\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Filtra toda consulta por el establecimiento activo. Si no hay ninguno
 * (nadie autenticado ni contexto fijado) NO devuelve nada: ante la duda,
 * nunca se filtran datos de otra cooperadora.
 */
class EstablecimientoScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $id = Tenant::id();

        if ($id === null) {
            $builder->whereRaw('1 = 0');

            return;
        }

        $builder->where($model->qualifyColumn('establecimiento_id'), $id);
    }
}
