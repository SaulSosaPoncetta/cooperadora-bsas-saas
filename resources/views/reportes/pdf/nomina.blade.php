<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        h1 { font-size: 15px; margin-bottom: 4px; }
        p.subtitulo { color: #555; margin-top: 0; margin-bottom: 16px; }
        table.nomina { width: 100%; border-collapse: collapse; }
        table.nomina th { background: #f2f2f2; text-align: left; padding: 6px; border-bottom: 1px solid #ccc; font-size: 10px; }
        table.nomina td { padding: 6px; border-bottom: 1px solid #eee; font-size: 10px; }
    </style>
</head>
<body>
    @include('reportes.pdf._encabezado')

    <h1>Nómina de la Comisión Directiva</h1>
    <p class="subtitulo">Vigente al {{ $fecha->format('d/m/Y') }} — Art. 5° inc. f) del Decreto 4767/72</p>

    <table class="nomina">
        <thead>
            <tr>
                <th>Cargo</th>
                <th>Apellido y Nombre</th>
                <th>DNI</th>
                <th>Domicilio</th>
                <th>Inicio de mandato</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($miembros as $miembro)
                <tr>
                    <td>{{ $miembro->etiquetaCargo() }}</td>
                    <td>{{ $miembro->socio->nombreCompleto() }}</td>
                    <td>{{ $miembro->socio->dni }}</td>
                    <td>{{ $miembro->socio->domicilio ?: '—' }}</td>
                    <td>{{ $miembro->fecha_inicio->format('d/m/Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="5">No hay miembros vigentes registrados.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
