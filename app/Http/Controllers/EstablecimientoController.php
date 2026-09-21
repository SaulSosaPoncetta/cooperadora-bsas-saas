<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManejaExcepciones;
use App\Models\Establecimiento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EstablecimientoController extends Controller
{
    use ManejaExcepciones;

    public function edit(): View
    {
        return $this->ejecutarVista(function () {
            return view('establecimiento.edit', [
                'establecimiento' => Establecimiento::actual(),
            ]);
        }, 'No se pudieron cargar los datos del establecimiento.');
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'cue' => ['nullable', 'string', 'max:50'],
            'domicilio' => ['nullable', 'string', 'max:255'],
            'distrito' => ['nullable', 'string', 'max:255'],
            'localidad' => ['nullable', 'string', 'max:255'],
            'provincia' => ['nullable', 'string', 'max:255'],
            'caracter_cuota' => ['required', 'in:mensual,anual'],
            'monto_cuota' => ['required', 'numeric', 'min:0'],
            'monto_caja_chica_autorizado' => ['required', 'numeric', 'min:0'],
        ]);

        return $this->ejecutar(function () use ($validated) {
            Establecimiento::actual()->update($validated);

            return redirect()->route('establecimiento.edit')
                ->with('status', 'establecimiento-actualizado');
        }, 'No se pudieron guardar los datos del establecimiento.');
    }
}
