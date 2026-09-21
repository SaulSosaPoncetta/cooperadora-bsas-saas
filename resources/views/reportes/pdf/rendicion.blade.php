<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        h1 { font-size: 16px; margin-bottom: 2px; }
        h2 { font-size: 13px; margin-top: 24px; margin-bottom: 6px; border-bottom: 1px solid #ccc; padding-bottom: 3px; }
        p.subtitulo { color: #555; margin-top: 0; margin-bottom: 16px; }
        p.memoria { white-space: pre-line; text-align: justify; }
        table.movimientos, table.nomina { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.movimientos th, table.nomina th { background: #f2f2f2; text-align: left; padding: 5px; border-bottom: 1px solid #ccc; font-size: 10px; }
        table.movimientos td, table.nomina td { padding: 5px; border-bottom: 1px solid #eee; font-size: 10px; }
        .ingreso { color: #15803d; }
        .egreso { color: #b91c1c; }
        .totales { margin-top: 10px; width: 100%; }
        .totales td { padding: 4px 8px; font-size: 11px; }
        .firmas { margin-top: 50px; width: 100%; }
        .firmas td { text-align: center; font-size: 10px; padding-top: 30px; border-top: 1px solid #333; }
    </style>
</head>
<body>
    @include('reportes.pdf._encabezado')

    <h1>Rendición Anual — Asamblea Ordinaria</h1>
    <p class="subtitulo">
        Asamblea del {{ $asamblea->fecha->format('d/m/Y') }} — Art. 44° del Estatuto y Art. 32°/33° del Decreto 4767/72
    </p>

    <h2>Memoria del ejercicio</h2>
    <p class="memoria">{{ $asamblea->memoria }}</p>

    <h2>Balance — período {{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}</h2>
    <table class="movimientos">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Categoría</th>
                <th>Concepto</th>
                <th style="text-align: right;">Monto</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($movimientos as $movimiento)
                <tr>
                    <td>{{ $movimiento->fecha->format('d/m/Y') }}</td>
                    <td class="{{ $movimiento->tipo }}">{{ ucfirst($movimiento->tipo) }}</td>
                    <td>{{ $movimiento->etiquetaCategoria() }}</td>
                    <td>{{ $movimiento->concepto }}</td>
                    <td class="{{ $movimiento->tipo }}" style="text-align: right;">$ {{ number_format($movimiento->monto, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="5">Sin movimientos en el período.</td></tr>
            @endforelse
        </tbody>
    </table>
    <table class="totales">
        <tr>
            <td><strong>Total Ingresos:</strong></td>
            <td class="ingreso">$ {{ number_format($totalIngresos, 2, ',', '.') }}</td>
            <td><strong>Total Egresos:</strong></td>
            <td class="egreso">$ {{ number_format($totalEgresos, 2, ',', '.') }}</td>
            <td><strong>Saldo:</strong></td>
            <td><strong>$ {{ number_format($totalIngresos - $totalEgresos, 2, ',', '.') }}</strong></td>
        </tr>
    </table>

    <h2>Nómina de la Comisión Directiva</h2>
    <table class="nomina">
        <thead>
            <tr>
                <th>Cargo</th>
                <th>Apellido y Nombre</th>
                <th>DNI</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($miembros as $miembro)
                <tr>
                    <td>{{ $miembro->etiquetaCargo() }}</td>
                    <td>{{ $miembro->socio->nombreCompleto() }}</td>
                    <td>{{ $miembro->socio->dni }}</td>
                </tr>
            @empty
                <tr><td colspan="3">No hay miembros vigentes registrados.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Referencia del Acta</h2>
    <p class="memoria">{{ $asamblea->resumen_acta }}</p>

    <table class="firmas">
        <tr>
            <td>Presidente/a</td>
            <td>Secretario/a</td>
            <td>Tesorero/a</td>
        </tr>
    </table>
</body>
</html>
