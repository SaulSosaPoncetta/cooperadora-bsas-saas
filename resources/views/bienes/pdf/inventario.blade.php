<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
        h1 { font-size: 15px; margin-bottom: 4px; }
        p.subtitulo { color: #555; margin-top: 0; margin-bottom: 10px; }
        table.inventario { width: 100%; border-collapse: collapse; }
        table.inventario th { background: #f2f2f2; text-align: left; padding: 5px; border: 1px solid #ccc; font-size: 9px; }
        table.inventario td { padding: 5px; border: 1px solid #ddd; font-size: 9px; vertical-align: top; }
        .estado-baja { color: #b91c1c; }
        .estado-cedido { color: #b45309; }
        .estado-uso { color: #15803d; }
        .totales { margin-top: 10px; width: 100%; }
        .totales td { padding: 4px 8px; font-size: 11px; }
        .firmas { margin-top: 50px; width: 100%; }
        .firmas td { text-align: center; font-size: 10px; padding-top: 30px; border-top: 1px solid #333; }
        .grupo-titulo { background: #ede9fe; font-weight: bold; padding: 4px 6px; margin-top: 14px; font-size: 11px; }
    </style>
</head>
<body>
    @include('reportes.pdf._encabezado')

    <h1>Inventario de Bienes — Control físico</h1>
    <p class="subtitulo">
        Generado el {{ $fecha->format('d/m/Y') }}
        @if (! empty($filtros['tipo']))
            — Tipo: {{ ucfirst($filtros['tipo']) }}
        @endif
        @if (! empty($filtros['estado']))
            — Estado: {{ $filtros['estado'] }}
        @endif
    </p>

    @foreach (['mueble' => 'Bienes Muebles', 'inmueble' => 'Bienes Inmuebles'] as $tipoValor => $tipoTitulo)
        @php $bienesTipo = $bienes->where('tipo', $tipoValor); @endphp
        @if ($bienesTipo->isNotEmpty())
            <div class="grupo-titulo">{{ $tipoTitulo }} ({{ $bienesTipo->count() }})</div>
            <table class="inventario">
                <thead>
                    <tr>
                        <th style="width: 22%;">Descripción</th>
                        <th style="width: 10%;">Adquisición</th>
                        <th style="width: 10%;">Origen</th>
                        <th style="width: 10%; text-align: right;">Valor</th>
                        <th style="width: 13%;">Estado</th>
                        <th style="width: 8%; text-align: center;">Verificado</th>
                        <th style="width: 27%;">Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bienesTipo as $bien)
                        <tr>
                            <td>{{ $bien->descripcion }}</td>
                            <td>{{ $bien->fecha_adquisicion->format('d/m/Y') }}</td>
                            <td>{{ \App\Models\Bien::ORIGENES[$bien->origen] ?? $bien->origen }}</td>
                            <td style="text-align: right;">{{ $bien->valor_estimado ? '$ '.number_format($bien->valor_estimado, 2, ',', '.') : '—' }}</td>
                            <td>
                                @switch($bien->estado)
                                    @case('en_uso') <span class="estado-uso">En uso</span> @break
                                    @case('cedido') <span class="estado-cedido">Cedido a {{ $bien->cedido_a }}</span> @break
                                    @case('dado_de_baja') <span class="estado-baja">De baja ({{ $bien->tipo_baja }})</span> @break
                                @endswitch
                            </td>
                            <td style="text-align: center;">&#9744; Sí &nbsp; &#9744; No</td>
                            <td>&nbsp;</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endforeach

    <table class="totales">
        <tr>
            <td><strong>Total de bienes listados:</strong></td>
            <td>{{ $bienes->count() }}</td>
            <td><strong>Valor estimado del patrimonio vigente:</strong></td>
            <td>$ {{ number_format($valorTotal, 2, ',', '.') }}</td>
        </tr>
    </table>

    <table class="firmas">
        <tr>
            <td>Presidente/a</td>
            <td>Tesorero/a</td>
            <td>Revisor/a de Cuentas</td>
        </tr>
    </table>
</body>
</html>
