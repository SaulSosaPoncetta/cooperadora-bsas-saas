<?php

namespace Database\Seeders;

use App\Models\Establecimiento;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Crea una cooperadora de demostración con su Presidente/a (administrador/a).
     * Sólo para entornos de desarrollo: en producción las cooperadoras se dan
     * de alta desde el registro o desde el panel de gestión.
     */
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        $establecimiento = Establecimiento::factory()->create([
            'nombre' => 'Escuela de Demostración N° 1',
        ]);

        $presidente = User::factory()->create([
            'name' => 'Presidente Demo',
            'email' => 'test@example.com',
            'establecimiento_id' => $establecimiento->id,
        ]);

        $presidente->assignRole('Presidente');
    }
}
