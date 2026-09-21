<?php

namespace App\Support;

use App\Models\Establecimiento;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

/**
 * Contexto de "inquilino" (tenant): cada Asociación Cooperadora / establecimiento
 * es un inquilino y sólo puede ver sus propios datos.
 *
 * En una request web el establecimiento activo es el del usuario autenticado.
 * Fuera de una request (seeders, comandos, panel) se puede fijar
 * explícitamente con Tenant::como().
 */
class Tenant
{
    private static bool $forzado = false;

    private static ?int $idForzado = null;

    public static function id(): ?int
    {
        if (self::$forzado) {
            return self::$idForzado;
        }

        $usuario = auth()->user();

        return $usuario && $usuario->establecimiento_id
            ? (int) $usuario->establecimiento_id
            : null;
    }

    public static function establecimiento(): ?Establecimiento
    {
        $id = self::id();

        return $id ? Establecimiento::find($id) : null;
    }

    /**
     * Ejecuta $accion con un establecimiento fijo, ignorando al usuario
     * autenticado, y restaura el contexto anterior al terminar.
     */
    public static function como(Establecimiento|int|null $establecimiento, callable $accion): mixed
    {
        $forzadoAntes = self::$forzado;
        $idAntes = self::$idForzado;

        self::$forzado = true;
        self::$idForzado = $establecimiento instanceof Establecimiento
            ? $establecimiento->id
            : $establecimiento;

        try {
            return $accion();
        } finally {
            self::$forzado = $forzadoAntes;
            self::$idForzado = $idAntes;
        }
    }

    /**
     * Regla de validación "exists" limitada al establecimiento activo, para
     * que no se pueda referenciar un registro de otra cooperadora.
     */
    public static function existe(string $tabla, string $columna = 'id'): Exists
    {
        return Rule::exists($tabla, $columna)
            ->where('establecimiento_id', self::id() ?? 0);
    }
}
