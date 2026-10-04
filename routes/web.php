<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(auth()->check() ? 'dashboard' : 'login'));
// guest показывает формы входа/регистрации только неавторизованным посетителям.
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->middleware('throttle:login');
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'store'])->middleware('throttle:6,1');
});
// auth требует входа, auth.session проверяет актуальность пароля в текущей сессии.
Route::middleware(['auth', 'auth.session'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    // resource создаёт семь стандартных CRUD-маршрутов: index, create, store, show, edit, update, destroy.
    Route::resource('tickets', TicketController::class);
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
