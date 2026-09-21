<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Roles y permisos según el Decreto 4767/72 y el Estatuto Modelo
     * de Asociaciones Cooperadoras Escolares (Provincia de Buenos Aires).
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permisos del sistema, agrupados por módulo
        $permissions = [
            // Establecimiento / configuración general
            'gestionar establecimiento',

            // Usuarios de la cooperadora (sólo el/la Presidente/a, que es
            // el/la administrador/a de su institución)
            'gestionar usuarios',

            // Socios (Art. 19° a 22° Estatuto)
            'ver socios',
            'gestionar socios',

            // Comisión Directiva (Art. 2° a 11° Estatuto)
            'ver comision directiva',
            'gestionar comision directiva',

            // Asambleas y Actas (Art. 23° a 41° Estatuto)
            'ver asambleas',
            'gestionar asambleas',
            'firmar actas',

            // Tesorería / Cuotas (Art. 9°, 13° a 15° Estatuto)
            'ver tesoreria',
            'gestionar tesoreria',
            'autorizar pagos',

            // Bienes muebles e inmuebles (Art. 16° a 18° Estatuto)
            'ver bienes',
            'gestionar bienes',

            // Reportes y rendiciones a la Dirección de Cooperación Escolar
            'ver reportes',
            'gestionar reportes',

            // Comisión Revisora de Cuentas (Art. 12° Estatuto)
            'auditar cuentas',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Roles según el Estatuto (Art. 3°: Presidente, Secretario, Tesorero,
        // 3 Vocales Titulares, 2 Vocales Suplentes; más Asesor y Revisor de Cuentas)

        $presidente = Role::findOrCreate('Presidente');
        $presidente->syncPermissions(Permission::all());

        $secretario = Role::findOrCreate('Secretario');
        $secretario->syncPermissions([
            'ver socios', 'gestionar socios',
            'ver comision directiva', 'gestionar comision directiva',
            'ver asambleas', 'gestionar asambleas', 'firmar actas',
            'ver tesoreria', 'autorizar pagos',
            'ver bienes',
            'ver reportes', 'gestionar reportes',
        ]);

        $tesorero = Role::findOrCreate('Tesorero');
        $tesorero->syncPermissions([
            'ver socios',
            'ver asambleas',
            'ver tesoreria', 'gestionar tesoreria', 'autorizar pagos',
            'ver bienes', 'gestionar bienes',
            'ver reportes',
        ]);

        $vocalTitular = Role::findOrCreate('Vocal Titular');
        $vocalTitular->syncPermissions([
            'ver socios',
            'ver comision directiva',
            'ver asambleas',
            'ver tesoreria',
            'ver bienes',
            'ver reportes',
        ]);

        $vocalSuplente = Role::findOrCreate('Vocal Suplente');
        $vocalSuplente->syncPermissions([
            'ver comision directiva',
            'ver asambleas',
        ]);

        $revisorCuentas = Role::findOrCreate('Revisor de Cuentas');
        $revisorCuentas->syncPermissions([
            'ver asambleas',
            'ver tesoreria',
            'ver bienes',
            'ver reportes',
            'auditar cuentas',
        ]);

        $asesor = Role::findOrCreate('Asesor');
        $asesor->syncPermissions([
            'ver socios',
            'ver comision directiva',
            'ver asambleas',
            'ver tesoreria',
            'ver bienes',
            'ver reportes',
        ]);
    }
}
