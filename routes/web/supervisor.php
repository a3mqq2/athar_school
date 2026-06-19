<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Supervisor\DashboardController;

Route::middleware(['role:supervisor'])->prefix('supervisor')->name('supervisor.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/check-user', [DashboardController::class, 'checkUser'])->name('check.user');
    Route::post('/record-attendance', [DashboardController::class, 'recordAttendance'])->name('record.attendance');
    
    // Logs routes
    Route::get('/logs', [DashboardController::class, 'logs'])->name('logs');
    Route::get('/logs/export', [DashboardController::class, 'exportLogs'])->name('logs.export');
    Route::get('/logs/{id}', [DashboardController::class, 'logDetails'])->name('logs.details');
    Route::delete('/logs/{id}', [DashboardController::class, 'deleteLog'])->name('logs.delete');
});