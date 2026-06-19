<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;


/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Redirect root to dashboard
Route::redirect('/', '/sections');

// Authentication Routes
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'submit'])->name('login.submit');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    
 
 Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', function () {
        auth()->logout();
        return redirect()->route('login')->with('success', 'تم تسجيل الخروج بنجاح');
    })->name('logout');


    Route::get('sections', [DashboardController::class, 'sections'])->name('sections');


    include __DIR__ . '/web/admin.php';
    include __DIR__ . '/web/supervisor.php';
    include __DIR__ . '/web/finance.php';

});
