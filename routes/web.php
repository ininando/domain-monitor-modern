<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\OpdController;
use App\Http\Controllers\PemetaanController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::redirect('/', '/dashboard')->name('home');

    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    // Super Admin dan Admin.
    Route::middleware('role:super_admin,admin')->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');

        Route::get('pemetaan', [PemetaanController::class, 'index'])->name('pemetaan.index');
        Route::get('pemetaan/export', [PemetaanController::class, 'export'])->name('pemetaan.export');
        Route::get('pemetaan/{opd}', [PemetaanController::class, 'show'])->name('pemetaan.show');

        Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('laporan/pdf', [LaporanController::class, 'pdf'])->name('laporan.pdf');
        Route::get('laporan/excel', [LaporanController::class, 'excel'])->name('laporan.excel');

        Route::resource('opd', OpdController::class)
            ->except('show')
            ->parameters(['opd' => 'opd']);

        Route::resource('domains', DomainController::class);
    });

    // Khusus Super Admin.
    Route::middleware('role:super_admin')->group(function () {
        Route::resource('users', UserController::class)->except('show');
    });
});
