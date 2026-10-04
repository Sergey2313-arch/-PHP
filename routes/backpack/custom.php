<?php

use App\Http\Controllers\Admin\CategoryCrudController;
use App\Http\Controllers\Admin\TicketCrudController;
use App\Http\Controllers\Admin\TicketStatusController;
use App\Http\Controllers\Admin\UserCrudController;
use Illuminate\Support\Facades\Route;

// web включает сессии и CSRF; backpack_middleware() дополнительно проверяет роль admin.
Route::group(['prefix' => config('backpack.base.route_prefix'), 'middleware' => ['web', backpack_middleware()]], function () {
    Route::get('/', fn () => redirect(backpack_url('ticket')))->name('backpack');
    Route::get('/dashboard', fn () => redirect(backpack_url('ticket')))->name('backpack.dashboard');
    Route::crud('user', UserCrudController::class);
    // Отдельный PATCH меняет только статус; обычный CRUD редактирует всю заявку.
    Route::patch('ticket/{ticket}/status', TicketStatusController::class)->name('admin.ticket.status');
    Route::crud('ticket', TicketCrudController::class);
    Route::crud('category', CategoryCrudController::class);
    Route::get('/edit-account-info', fn () => redirect()->route('profile.edit'))->name('backpack.account.info');
});
Route::middleware('web')->get('/admin/login', fn () => redirect()->route('login'))->name('backpack.auth.login');
// Теме нужен адрес logout, но GET только открывает профиль: выход выполняется через POST + CSRF.
Route::middleware(['web', 'auth'])->get('/admin/logout', fn () => redirect()->route('profile.edit'))->name('backpack.auth.logout');
