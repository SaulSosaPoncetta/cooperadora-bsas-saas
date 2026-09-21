<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Red de seguridad: el webhook del panel actualiza al instante, esto corrige
// cualquier aviso que se haya perdido.
Schedule::command('panel:sincronizar')->hourly()->withoutOverlapping();
