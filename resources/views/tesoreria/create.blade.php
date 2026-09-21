<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">{{ $tipo === 'egreso' ? 'Nuevo Egreso' : 'Nuevo Ingreso' }}</h2>
    </x-slot>

    <div class="card border-0 shadow-sm" style="max-width: 640px;">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('tesoreria.store') }}">
                @csrf
                <input type="hidden" name="tipo" value="{{ $tipo }}">

                <div class="btn-group mb-3" role="group">
                    <a href="{{ route('tesoreria.create', ['tipo' => 'ingreso']) }}" class="btn btn-sm {{ $tipo === 'ingreso' ? 'btn-success' : 'btn-outline-secondary' }}">Ingreso</a>
                    <a href="{{ route('tesoreria.create', ['tipo' => 'egreso']) }}" class="btn btn-sm {{ $tipo === 'egreso' ? 'btn-danger' : 'btn-outline-secondary' }}">Egreso</a>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <x-input-label for="fecha" value="Fecha" />
                        <x-text-input id="fecha" name="fecha" type="date" :value="old('fecha', now()->toDateString())" required />
                        <x-input-error :messages="$errors->get('fecha')" />
                    </div>

                    <div class="col-md-6">
                        <x-input-label for="monto" value="Monto ($)" />
                        <x-text-input id="monto" name="monto" type="number" step="0.01" min="0.01" :value="old('monto')" required />
                        <x-input-error :messages="$errors->get('monto')" />
                    </div>
                </div>

                <div class="mt-3">
                    <x-input-label for="categoria" value="Categoría" />
                    <select id="categoria" name="categoria" class="form-select" required>
                        <option value="">Seleccionar...</option>
                        @foreach ($categorias as $valor => $etiqueta)
                            <option value="{{ $valor }}" @selected(old('categoria') === $valor)>{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('categoria')" />
                </div>

                <div class="mt-3">
                    <x-input-label for="concepto" value="Concepto / detalle" />
                    <x-text-input id="concepto" name="concepto" type="text" :value="old('concepto')" required />
                    <x-input-error :messages="$errors->get('concepto')" />
                </div>

                <div class="mt-3">
                    <x-input-label for="comprobante_numero" value="N° de comprobante / factura" />
                    <x-text-input id="comprobante_numero" name="comprobante_numero" type="text" :value="old('comprobante_numero')" />
                    <x-input-error :messages="$errors->get('comprobante_numero')" />
                    <div class="form-text">Art. 9° inc. f) del Estatuto: los pagos deben respaldarse con factura.</div>
                </div>

                @if ($tipo === 'ingreso')
                    <div class="mt-3">
                        <x-input-label for="socio_id" value="Socio/a (si corresponde a una cuota)" />
                        <select id="socio_id" name="socio_id" class="form-select">
                            <option value="">No corresponde</option>
                            @foreach ($socios as $socio)
                                <option value="{{ $socio->id }}" @selected(old('socio_id') == $socio->id)>{{ $socio->nombreCompleto() }} — DNI {{ $socio->dni }} ({{ $socio->cuotas_adeudadas }} adeudadas)</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('socio_id')" />
                        <div class="form-text">Si la categoría es "Cuota Social" y elegís un socio/a, se descuenta 1 cuota adeudada automáticamente.</div>
                    </div>
                @else
                    <div class="mt-3">
                        <x-input-label for="autorizado_por" value="Autoriza (firma conjunta)" />
                        <select id="autorizado_por" name="autorizado_por" class="form-select" required>
                            <option value="">Seleccionar...</option>
                            @foreach ($firmantes as $firmante)
                                <option value="{{ $firmante->id }}" @selected(old('autorizado_por') == $firmante->id)>{{ $firmante->etiquetaCargo() }} — {{ $firmante->socio->nombreCompleto() }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('autorizado_por')" />
                        <div class="form-text">Art. 14° del Estatuto: la extracción de fondos requiere la firma de dos entre Presidente/a, Secretario/a y Tesorero/a.</div>
                    </div>
                @endif

                <div class="d-flex align-items-center gap-3 mt-4">
                    <x-primary-button>Guardar</x-primary-button>
                    <a href="{{ route('tesoreria.index') }}" class="text-decoration-none text-muted">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
