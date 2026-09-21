<table width="100%" style="margin-bottom: 16px; border-bottom: 2px solid #333; padding-bottom: 8px;">
    <tr>
        <td style="font-size: 14px; font-weight: bold;">
            Asociación Cooperadora Escolar
        </td>
        <td style="text-align: right; font-size: 10px; color: #555;">
            Decreto 4767/72
        </td>
    </tr>
</table>
<table width="100%" style="margin-bottom: 16px; font-size: 10px; color: #333;">
    <tr>
        <td><strong>Establecimiento:</strong> {{ $establecimiento->nombre ?: '—' }}</td>
        <td><strong>CUE:</strong> {{ $establecimiento->cue ?: '—' }}</td>
    </tr>
    <tr>
        <td><strong>Domicilio:</strong> {{ $establecimiento->domicilio ?: '—' }}</td>
        <td><strong>Distrito:</strong> {{ $establecimiento->distrito ?: '—' }}</td>
    </tr>
    <tr>
        <td><strong>Localidad:</strong> {{ $establecimiento->localidad ?: '—' }}</td>
        <td><strong>Provincia:</strong> {{ $establecimiento->provincia }}</td>
    </tr>
</table>
