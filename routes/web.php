<?php

use App\Http\Controllers\AsambleaController;
use App\Http\Controllers\BienController;
use App\Http\Controllers\ComisionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EstablecimientoController;
use App\Http\Controllers\MovimientoTesoreriaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\SocioController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'dashboard' : 'login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'permission:gestionar establecimiento'])->group(function () {
    Route::get('/establecimiento', [EstablecimientoController::class, 'edit'])->name('establecimiento.edit');
    Route::put('/establecimiento', [EstablecimientoController::class, 'update'])->name('establecimiento.update');
});

Route::middleware(['auth', 'permission:ver socios'])->group(function () {
    Route::get('/socios', [SocioController::class, 'index'])->name('socios.index');
});

Route::middleware(['auth', 'permission:gestionar socios'])->group(function () {
    Route::get('/socios/nuevo', [SocioController::class, 'create'])->name('socios.create');
    Route::post('/socios', [SocioController::class, 'store'])->name('socios.store');
    Route::get('/socios/{socio}/editar', [SocioController::class, 'edit'])->name('socios.edit');
    Route::put('/socios/{socio}', [SocioController::class, 'update'])->name('socios.update');
    Route::patch('/socios/{socio}/baja', [SocioController::class, 'baja'])->name('socios.baja');
    Route::patch('/socios/{socio}/reactivar', [SocioController::class, 'reactivar'])->name('socios.reactivar');
});

Route::middleware(['auth', 'permission:ver comision directiva'])->group(function () {
    Route::get('/comision', [ComisionController::class, 'index'])->name('comision.index');
});

Route::middleware(['auth', 'permission:gestionar comision directiva'])->group(function () {
    Route::get('/comision/nuevo', [ComisionController::class, 'create'])->name('comision.create');
    Route::post('/comision', [ComisionController::class, 'store'])->name('comision.store');
    Route::patch('/comision/{miembro}/cese', [ComisionController::class, 'cese'])->name('comision.cese');
});

Route::middleware(['auth', 'permission:ver tesoreria'])->group(function () {
    Route::get('/tesoreria', [MovimientoTesoreriaController::class, 'index'])->name('tesoreria.index');
});

Route::middleware(['auth', 'permission:gestionar tesoreria'])->group(function () {
    Route::get('/tesoreria/nuevo', [MovimientoTesoreriaController::class, 'create'])->name('tesoreria.create');
    Route::post('/tesoreria', [MovimientoTesoreriaController::class, 'store'])->name('tesoreria.store');
});

Route::middleware(['auth', 'permission:ver asambleas'])->group(function () {
    Route::get('/asambleas', [AsambleaController::class, 'index'])->name('asambleas.index');
    Route::get('/asambleas/{asamblea}', [AsambleaController::class, 'show'])->name('asambleas.show');
});

Route::middleware(['auth', 'permission:gestionar asambleas'])->group(function () {
    Route::get('/asambleas/nueva', [AsambleaController::class, 'create'])->name('asambleas.create');
    Route::post('/asambleas', [AsambleaController::class, 'store'])->name('asambleas.store');
    Route::post('/asambleas/{asamblea}/asistencia', [AsambleaController::class, 'asistencia'])->name('asambleas.asistencia');
    Route::post('/asambleas/{asamblea}/realizar', [AsambleaController::class, 'realizar'])->name('asambleas.realizar');
    Route::post('/asambleas/{asamblea}/anular', [AsambleaController::class, 'anular'])->name('asambleas.anular');
});

Route::middleware(['auth', 'permission:firmar actas'])->group(function () {
    Route::post('/asambleas/{asamblea}/elevar', [AsambleaController::class, 'elevar'])->name('asambleas.elevar');
});

Route::middleware(['auth', 'permission:ver bienes'])->group(function () {
    Route::get('/bienes', [BienController::class, 'index'])->name('bienes.index');
    Route::get('/bienes/inventario.pdf', [BienController::class, 'inventarioPdf'])->name('bienes.inventario.pdf');
});

Route::middleware(['auth', 'permission:gestionar bienes'])->group(function () {
    Route::get('/bienes/nuevo', [BienController::class, 'create'])->name('bienes.create');
    Route::post('/bienes', [BienController::class, 'store'])->name('bienes.store');
    Route::patch('/bienes/{bien}/ceder', [BienController::class, 'ceder'])->name('bienes.ceder');
    Route::patch('/bienes/{bien}/devolver', [BienController::class, 'devolver'])->name('bienes.devolver');
    Route::patch('/bienes/{bien}/baja', [BienController::class, 'baja'])->name('bienes.baja');
});

Route::middleware(['auth', 'permission:ver reportes'])->group(function () {
    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
    Route::get('/reportes/balance.pdf', [ReporteController::class, 'balancePdf'])->name('reportes.balance.pdf');
    Route::get('/reportes/nomina.pdf', [ReporteController::class, 'nominaPdf'])->name('reportes.nomina.pdf');
    Route::get('/reportes/rendicion/{asamblea}.pdf', [ReporteController::class, 'rendicionPdf'])->name('reportes.rendicion.pdf');
});

Route::middleware(['auth', 'permission:gestionar reportes'])->group(function () {
    Route::get('/reportes/memoria/{asamblea}', [ReporteController::class, 'memoriaEdit'])->name('reportes.memoria.edit');
    Route::put('/reportes/memoria/{asamblea}', [ReporteController::class, 'memoriaUpdate'])->name('reportes.memoria.update');
});

require __DIR__.'/auth.php';
