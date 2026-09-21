<?php

namespace Database\Factories;

use App\Models\Establecimiento;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Establecimiento>
 */
class EstablecimientoFactory extends Factory
{
    protected $model = Establecimiento::class;

    public function definition(): array
    {
        return [
            'nombre' => 'Escuela N° '.fake()->unique()->numberBetween(1, 9999),
            'cue' => fake()->numerify('#########'),
            'distrito' => fake()->city(),
            'localidad' => fake()->city(),
            'provincia' => 'Buenos Aires',
            'estado_suscripcion' => Establecimiento::ESTADO_ACTIVA,
            'prueba_hasta' => null,
            'suscripcion_vence_el' => null,
        ];
    }

    public function enPrueba(int $dias = 30): static
    {
        return $this->state(fn () => [
            'estado_suscripcion' => Establecimiento::ESTADO_PRUEBA,
            'prueba_hasta' => now()->addDays($dias)->toDateString(),
        ]);
    }

    public function vencida(int $diasAtras = 30): static
    {
        return $this->state(fn () => [
            'estado_suscripcion' => Establecimiento::ESTADO_ACTIVA,
            'suscripcion_vence_el' => now()->subDays($diasAtras)->toDateString(),
        ]);
    }

    public function suspendida(): static
    {
        return $this->state(fn () => [
            'estado_suscripcion' => Establecimiento::ESTADO_SUSPENDIDA,
        ]);
    }
}
