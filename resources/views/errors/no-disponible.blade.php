<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'MiGestión Cooperadoras') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/theme-violeta.css') }}" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="min-vh-100 d-flex align-items-center justify-content-center py-5">
        <div class="w-100 text-center" style="max-width: 480px;">
            <div class="bg-danger text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                 style="width:64px;height:64px;">
                <i class="bi bi-exclamation-triangle fs-2"></i>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-2">No pudimos mostrar esta página</h5>
                    <p class="text-muted small mb-4">
                        {{ $mensaje ?? 'Hubo un problema al cargar los datos. Verificá tu conexión e intentá nuevamente.' }}
                    </p>
                    <a href="{{ url('/') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-house me-1"></i>Volver al inicio
                    </a>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="location.reload()">
                        <i class="bi bi-arrow-clockwise me-1"></i>Reintentar
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
