<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManejaExcepciones;
use App\Models\Socio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SocioController extends Controller
{
    use ManejaExcepciones;

    public function index(Request $request): View
    {
        return $this->ejecutarVista(function () use ($request) {
            $socios = Socio::query()
                ->when($request->filled('busqueda'), function ($query) use ($request) {
                    $busqueda = $request->string('busqueda');
                    $query->where(function ($query) use ($busqueda) {
                        $query->where('nombre', 'like', "%{$busqueda}%")
                            ->orWhere('apellido', 'like', "%{$busqueda}%")
                            ->orWhere('dni', 'like', "%{$busqueda}%");
                    });
                })
                ->when($request->filled('categoria'), fn ($query) => $query->where('categoria', $request->string('categoria')))
                ->when($request->filled('estado'), fn ($query) => $query->where('activo', $request->string('estado') === 'activo'))
                ->orderBy('apellido')
                ->orderBy('nombre')
                ->paginate(20)
                ->withQueryString();

            return view('socios.index', [
                'socios' => $socios,
                'filtros' => $request->only(['busqueda', 'categoria', 'estado']),
            ]);
        }, 'No se pudo cargar el listado de socios/as.');
    }

    public function create(): View
    {
        return $this->ejecutarVista(function () {
            return view('socios.create', [
                'socio' => new Socio(['fecha_ingreso' => now()->toDateString()]),
            ]);
        }, 'No se pudo abrir el formulario de alta de socio/a.');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validado($request);

        return $this->ejecutar(function () use ($validated) {
            Socio::create($validated);

            return redirect()->route('socios.index')->with('status', 'socio-creado');
        }, 'No se pudo registrar el/la socio/a.');
    }

    public function edit(Socio $socio): View
    {
        return $this->ejecutarVista(fn () => view('socios.edit', ['socio' => $socio]), 'No se pudo abrir la ficha del/la socio/a.');
    }

    public function update(Request $request, Socio $socio): RedirectResponse
    {
        $validated = $this->validado($request, $socio);

        return $this->ejecutar(function () use ($validated, $socio) {
            $socio->update($validated);

            return redirect()->route('socios.index')->with('status', 'socio-actualizado');
        }, 'No se pudieron guardar los cambios del/la socio/a.');
    }

    /**
     * Art. 22° del Estatuto: la baja de un/a socio/a no es un borrado,
     * es un cambio de estado con motivo y fecha.
     */
    public function baja(Request $request, Socio $socio): RedirectResponse
    {
        $validated = $request->validate([
            'motivo_baja' => ['required', 'string', 'max:255'],
        ]);

        return $this->ejecutar(function () use ($validated, $socio) {
            $socio->update([
                'activo' => false,
                'fecha_baja' => now(),
                'motivo_baja' => $validated['motivo_baja'],
            ]);

            return redirect()->route('socios.index')->with('status', 'socio-dado-de-baja');
        }, 'No se pudo registrar la baja del/la socio/a.');
    }

    public function reactivar(Socio $socio): RedirectResponse
    {
        return $this->ejecutar(function () use ($socio) {
            $socio->update([
                'activo' => true,
                'fecha_baja' => null,
                'motivo_baja' => null,
            ]);

            return redirect()->route('socios.index')->with('status', 'socio-reactivado');
        }, 'No se pudo reactivar al/la socio/a.');
    }

    private function validado(Request $request, ?Socio $socio = null): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'dni' => ['required', 'string', 'max:20', 'unique:socios,dni,'.($socio?->id)],
            'domicilio' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'categoria' => ['required', 'in:activo,honorario,adherente'],
            'fecha_ingreso' => ['required', 'date'],
            'cuotas_adeudadas' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
