<?php

use App\Http\Controllers\Admin\AlertEventController;
use App\Http\Controllers\Admin\AlertRuleController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeviceController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\NotificationSettingsController;
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
        Route::get('devices/{device}', [DeviceController::class, 'show'])->name('devices.show');
        Route::get('devices/{device}/metrics', [DeviceController::class, 'metrics'])->name('devices.metrics');
        Route::post('devices/{device}/snmp-test', [DeviceController::class, 'snmpTest'])->name('devices.snmp-test');
        Route::post('devices/{device}/poll', [DeviceController::class, 'poll'])->name('devices.poll');
        Route::resource('alert-rules', AlertRuleController::class)->except(['show']);
        Route::get('alert-events', [AlertEventController::class, 'index'])->name('alert-events.index');
        Route::get('notification-settings', [NotificationSettingsController::class, 'edit'])->name('notification-settings.edit');
        Route::put('notification-settings', [NotificationSettingsController::class, 'update'])->name('notification-settings.update');
        Route::post('notification-settings/test-telegram', [NotificationSettingsController::class, 'testTelegram'])->name('notification-settings.test-telegram');
        Route::post('notification-settings/test-whatsapp', [NotificationSettingsController::class, 'testWhatsApp'])->name('notification-settings.test-whatsapp');
        Route::resource('users', UserController::class)->except(['show']);
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
