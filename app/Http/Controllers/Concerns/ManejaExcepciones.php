<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Centraliza el manejo de excepciones de los controladores: evita repetir
 * el mismo try/catch en cada acción y deja un único lugar donde ajustar
 * los mensajes y el registro de errores.
 */
trait ManejaExcepciones
{
    /**
     * Ejecuta $accion atrapando cualquier error inesperado (por ejemplo,
     * pérdida de conexión con la base de datos) y devuelve al usuario a
     * la pantalla anterior con un mensaje claro, en vez de romper con un
     * error 500. Las excepciones de validación se dejan pasar tal cual,
     * porque Laravel ya sabe mostrarlas junto al formulario.
     */
    protected function ejecutar(callable $accion, string $mensaje = 'No se pudo completar la operación. Verificá tu conexión e intentá nuevamente.')
    {
        try {
            return $accion();
        } catch (ValidationException $e) {
            throw $e;
        } catch (QueryException $e) {
            Log::error('Error de base de datos en '.static::class, [
                'mensaje' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'No se pudo guardar: hubo un problema de conexión con la base de datos. Intentá nuevamente en unos segundos.');
        } catch (Throwable $e) {
            Log::error('Error inesperado en '.static::class, [
                'mensaje' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', $mensaje);
        }
    }

    /**
     * Igual que ejecutar(), pero corriendo $accion dentro de una
     * transacción de base de datos: si se cae la conexión o falla
     * cualquier paso a mitad de camino, se deshace todo (no queda
     * ninguna escritura parcial) y no hay que "arreglar a mano" nada.
     */
    protected function ejecutarEnTransaccion(callable $accion, string $mensaje = 'No se pudo completar la operación. Verificá tu conexión e intentá nuevamente.')
    {
        return $this->ejecutar(fn () => DB::transaction($accion), $mensaje);
    }

    /**
     * Para acciones de LECTURA que devuelven una vista (index, show,
     * formularios). Si falla la consulta (por ejemplo, se cae la
     * conexión a la base de datos), muestra una página de error en vez
     * de romper con un 500 sin explicación.
     */
    protected function ejecutarVista(callable $accion, string $mensaje = 'No se pudieron cargar los datos. Verificá tu conexión e intentá nuevamente.')
    {
        try {
            return $accion();
        } catch (Throwable $e) {
            Log::error('Error al cargar una vista en '.static::class, [
                'mensaje' => $e->getMessage(),
            ]);

            return view('errors.no-disponible', ['mensaje' => $mensaje]);
        }
    }

    /**
     * Igual que ejecutarVista(), pero para acciones que devuelven una
     * respuesta HTTP "cruda" (por ejemplo, un PDF generado con dompdf).
     */
    protected function ejecutarRespuesta(callable $accion, string $mensaje = 'No se pudo generar el archivo. Verificá tu conexión e intentá nuevamente.')
    {
        try {
            return $accion();
        } catch (Throwable $e) {
            Log::error('Error al generar una respuesta en '.static::class, [
                'mensaje' => $e->getMessage(),
            ]);

            return response()->view('errors.no-disponible', ['mensaje' => $mensaje], 500);
        }
    }
}
