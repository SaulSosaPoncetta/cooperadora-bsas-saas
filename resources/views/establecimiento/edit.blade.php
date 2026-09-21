<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">Datos del Establecimiento</h2>
    </x-slot>

    <div class="card border-0 shadow-sm" style="max-width: 640px;">
        <div class="card-body p-4">
            <p class="text-muted small mb-4">
                Estos datos identifican al establecimiento escolar y se utilizan como encabezado en el Estatuto, actas y demás documentación de la Asociación Cooperadora.
            </p>

            @if (session('status') === 'establecimiento-actualizado')
                <div class="alert alert-success py-2 small">Datos guardados.</div>
            @endif

            <form method="POST" action="{{ route('establecimiento.update') }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <x-input-label for="nombre" value="Establecimiento" />
                    <x-text-input id="nombre" name="nombre" type="text" :value="old('nombre', $establecimiento->nombre)" required autofocus />
                    <x-input-error :messages="$errors->get('nombre')" />
                </div>

                <div class="mb-3">
                    <x-input-label for="cue" value="CUE (Código Único de Establecimiento)" />
                    <x-text-input id="cue" name="cue" type="text" :value="old('cue', $establecimiento->cue)" />
                    <x-input-error :messages="$errors->get('cue')" />
                </div>

                <div class="mb-3">
                    <x-input-label for="domicilio" value="Domicilio" />
                    <x-text-input id="domicilio" name="domicilio" type="text" :value="old('domicilio', $establecimiento->domicilio)" />
                    <x-input-error :messages="$errors->get('domicilio')" />
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <x-input-label for="distrito" value="Distrito" />
                        <x-text-input id="distrito" name="distrito" type="text" :value="old('distrito', $establecimiento->distrito)" />
                        <x-input-error :messages="$errors->get('distrito')" />
                    </div>

                    <div class="col-md-6">
                        <x-input-label for="localidad" value="Localidad" />
                        <x-text-input id="localidad" name="localidad" type="text" :value="old('localidad', $establecimiento->localidad)" />
                        <x-input-error :messages="$errors->get('localidad')" />
                    </div>
                </div>

                <div class="mt-3">
                    <x-input-label for="provincia" value="Provincia" />
                    <x-text-input id="provincia" name="provincia" type="text" :value="old('provincia', $establecimiento->provincia)" />
                    <x-input-error :messages="$errors->get('provincia')" />
                </div>

                <hr class="my-4">

                <p class="text-muted small">
                    Cuota social (Art. 15° del Estatuto). Su monto sólo puede modificarse por decisión de la Asamblea.
                </p>

                <div class="row g-3">
                    <div class="col-md-6">
                        <x-input-label for="caracter_cuota" value="Carácter de la cuota" />
                        <select id="caracter_cuota" name="caracter_cuota" class="form-select">
                            <option value="mensual" @selected(old('caracter_cuota', $establecimiento->caracter_cuota) === 'mensual')>Mensual</option>
                            <option value="anual" @selected(old('caracter_cuota', $establecimiento->caracter_cuota) === 'anual')>Anual</option>
                        </select>
                        <x-input-error :messages="$errors->get('caracter_cuota')" />
                    </div>

                    <div class="col-md-6">
                        <x-input-label for="monto_cuota" value="Monto de la cuota ($)" />
                        <x-text-input id="monto_cuota" name="monto_cuota" type="number" step="0.01" min="0" :value="old('monto_cuota', $establecimiento->monto_cuota)" required />
                        <x-input-error :messages="$errors->get('monto_cuota')" />
                    </div>
                </div>

                <div class="mt-3">
                    <x-input-label for="monto_caja_chica_autorizado" value="Caja Chica autorizada por Asamblea ($)" />
                    <x-text-input id="monto_caja_chica_autorizado" name="monto_caja_chica_autorizado" type="number" step="0.01" min="0" :value="old('monto_caja_chica_autorizado', $establecimiento->monto_caja_chica_autorizado)" required />
                    <x-input-error :messages="$errors->get('monto_caja_chica_autorizado')" />
                    <div class="form-text">Art. 23° inc. c) del Estatuto: monto en efectivo que la Asamblea autoriza mantener al/la Tesorero/a para gastos menores y urgentes.</div>
                </div>

                <div class="d-flex align-items-center gap-3 mt-4">
                    <x-primary-button>Guardar</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
