<div class="row g-3">
    <div class="col-md-6">
        <x-input-label for="nombre" value="Nombre" />
        <x-text-input id="nombre" name="nombre" type="text" :value="old('nombre', $socio->nombre)" required autofocus />
        <x-input-error :messages="$errors->get('nombre')" />
    </div>

    <div class="col-md-6">
        <x-input-label for="apellido" value="Apellido" />
        <x-text-input id="apellido" name="apellido" type="text" :value="old('apellido', $socio->apellido)" required />
        <x-input-error :messages="$errors->get('apellido')" />
    </div>
</div>

<div class="row g-3 mt-0">
    <div class="col-md-6">
        <x-input-label for="dni" value="DNI" />
        <x-text-input id="dni" name="dni" type="text" :value="old('dni', $socio->dni)" required />
        <x-input-error :messages="$errors->get('dni')" />
    </div>

    <div class="col-md-6">
        <x-input-label for="categoria" value="Categoría" />
        <select id="categoria" name="categoria" class="form-select">
            <option value="activo" @selected(old('categoria', $socio->categoria) === 'activo')>Activo/a</option>
            <option value="honorario" @selected(old('categoria', $socio->categoria) === 'honorario')>Honorario/a</option>
            <option value="adherente" @selected(old('categoria', $socio->categoria) === 'adherente')>Adherente</option>
        </select>
        <x-input-error :messages="$errors->get('categoria')" />
    </div>
</div>

<div class="mt-3">
    <x-input-label for="domicilio" value="Domicilio" />
    <x-text-input id="domicilio" name="domicilio" type="text" :value="old('domicilio', $socio->domicilio)" />
    <x-input-error :messages="$errors->get('domicilio')" />
</div>

<div class="row g-3 mt-0">
    <div class="col-md-6">
        <x-input-label for="telefono" value="Teléfono" />
        <x-text-input id="telefono" name="telefono" type="text" :value="old('telefono', $socio->telefono)" />
        <x-input-error :messages="$errors->get('telefono')" />
    </div>

    <div class="col-md-6">
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" name="email" type="email" :value="old('email', $socio->email)" />
        <x-input-error :messages="$errors->get('email')" />
    </div>
</div>

<div class="row g-3 mt-0">
    <div class="col-md-6">
        <x-input-label for="fecha_ingreso" value="Fecha de ingreso" />
        <x-text-input id="fecha_ingreso" name="fecha_ingreso" type="date" :value="old('fecha_ingreso', optional($socio->fecha_ingreso)->toDateString())" required />
        <x-input-error :messages="$errors->get('fecha_ingreso')" />
    </div>

    <div class="col-md-6">
        <x-input-label for="cuotas_adeudadas" value="Cuotas adeudadas" />
        <x-text-input id="cuotas_adeudadas" name="cuotas_adeudadas" type="number" min="0" :value="old('cuotas_adeudadas', $socio->cuotas_adeudadas ?? 0)" />
        <x-input-error :messages="$errors->get('cuotas_adeudadas')" />
        <div class="form-text">Art. 22° del Estatuto: se pierde la condición de socio/a con más de 3 cuotas adeudadas.</div>
    </div>
</div>
