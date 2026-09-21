<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'MiGestión Cooperadoras') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/theme-violeta.css') }}" rel="stylesheet">
</head>
<body class="bg-light">

<div class="min-vh-100 d-flex align-items-center justify-content-center py-5">
    <div class="w-100" style="max-width: 440px;">

        <div class="text-center mb-4">
            <a href="/" class="text-decoration-none">
                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                     style="width:64px;height:64px;">
                    <i class="bi bi-people-fill fs-2"></i>
                </div>
                <h3 class="fw-bold text-dark">{{ config('app.name', 'MiGestión Cooperadoras') }}</h3>
            </a>
            <p class="text-muted mb-0">Sistema de gestión para Asociaciones Cooperadoras</p>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                {{ $slot }}
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
