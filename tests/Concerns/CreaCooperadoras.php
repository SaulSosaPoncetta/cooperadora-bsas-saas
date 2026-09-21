<?php

namespace Tests\Concerns;

use App\Models\Establecimiento;
use App\Models\Socio;
use App\Models\User;
use App\Support\Tenant;

trait CreaCooperadoras
{
    protected function cooperadora(array $atributos = []): Establecimiento
    {
        return Establecimiento::factory()->create($atributos);
    }

    protected function usuarioDe(Establecimiento $cooperadora, string $rol = 'Presidente'): User
    {
        $usuario = User::factory()->create(['establecimiento_id' => $cooperadora->id]);
        $usuario->assignRole($rol);

        return $usuario;
    }

    protected function socioEn(Establecimiento $cooperadora, array $atributos = []): Socio
    {
        return Tenant::como($cooperadora, fn () => Socio::create(array_merge([
            'nombre' => 'Ana',
            'apellido' => 'Pérez',
            'dni' => (string) random_int(10000000, 99999999),
            'categoria' => 'activo',
            'fecha_ingreso' => now()->subYear()->toDateString(),
        ], $atributos)));
    }
}
