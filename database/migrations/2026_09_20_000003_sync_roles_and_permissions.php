<?php

use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Roles y permisos son datos de sistema (iguales para todas las
     * cooperadoras). Se sincronizan en cada despliegue con `migrate`, así
     * el permiso nuevo "gestionar usuarios" llega también a instalaciones
     * ya existentes y el registro de una cooperadora nunca encuentra el
     * rol Presidente sin crear.
     */
    public function up(): void
    {
        (new RolesAndPermissionsSeeder)->run();
    }

    public function down(): void
    {
        // Nada que revertir: los roles y permisos son datos de referencia.
    }
};
