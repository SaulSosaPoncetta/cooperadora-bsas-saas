<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        h1 { font-size: 15px; margin-bottom: 4px; }
        p.subtitulo { color: #555; margin-top: 0; margin-bottom: 16px; }
        table.movimientos { width: 100%; border-collapse: collapse; }
        table.movimientos th { background: #f2f2f2; text-align: left; padding: 5px; border-bottom: 1px solid #ccc; font-size: 10px; }
        table.movimientos td { padding: 5px; border-bottom: 1px solid #eee; font-size: 10px; }
        .ingreso { color: #15803d; }
        .egreso { color: #b91c1c; }
        .totales { margin-top: 16px; width: 100%; }
        .totales td { padding: 4px 8px; font-size: 11px; }
        .firmas { margin-top: 60px; width: 100%; }
        .firmas td { text-align: center; font-size: 10px; padding-top: 30px; border-top: 1px solid #333; }
    </style>
</head>
<body>
    @include('reportes.pdf._encabezado')

    <h1>Balance de Tesorería</h1>
    <p class="subtitulo">Período: {{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}</p>

    <table class="movimientos">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Categoría</th>
                <th>Concepto</th>
                <th>Comprobante</th>
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
                    <td>{{ $movimiento->comprobante_numero ?: '—' }}</td>
                    <td class="{{ $movimiento->tipo }}" style="text-align: right;">$ {{ number_format($movimiento->monto, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="6">Sin movimientos en el período.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="totales">
        <tr>
            <td><strong>Total Ingresos:</strong></td>
            <td class="ingreso">$ {{ number_format($totalIngresos, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Total Egresos:</strong></td>
            <td class="egreso">$ {{ number_format($totalEgresos, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Saldo del período:</strong></td>
            <td><strong>$ {{ number_format($totalIngresos - $totalEgresos, 2, ',', '.') }}</strong></td>
        </tr>
    </table>

    <table class="firmas">
        <tr>
            <td>Presidente/a</td>
            <td>Secretario/a</td>
            <td>Tesorero/a</td>
        </tr>
        <tr>
            <td style="padding-top: 40px; border-top: 1px solid #333;">Revisor/a de Cuentas</td>
            <td style="padding-top: 40px; border-top: 1px solid #333;">Asesor/a</td>
            <td></td>
        </tr>
    </table>
</body>
</html>
