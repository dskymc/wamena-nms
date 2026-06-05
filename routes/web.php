<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeviceController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\SnmpProfileController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('locations', LocationController::class)->except(['show']);
        Route::resource('snmp-profiles', SnmpProfileController::class)->except(['show']);
        Route::resource('devices', DeviceController::class)->except(['show']);
        Route::post('devices/{device}/snmp-test', [DeviceController::class, 'snmpTest'])->name('devices.snmp-test');
        Route::post('devices/{device}/poll', [DeviceController::class, 'poll'])->name('devices.poll');
        Route::resource('users', UserController::class)->except(['show']);
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
