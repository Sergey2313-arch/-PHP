<?php

use App\Http\Controllers\Admin\CategoryCrudController;
use App\Http\Controllers\Admin\TicketCrudController;
use App\Http\Controllers\Admin\UserCrudController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => config('backpack.base.route_prefix'), 'middleware' => ['web', backpack_middleware()]], function () {
    Route::get('/', fn () => redirect(backpack_url('ticket')))->name('backpack');
    Route::get('/dashboard', fn () => redirect(backpack_url('ticket')))->name('backpack.dashboard');
    Route::crud('user', UserCrudController::class);
    Route::crud('ticket', TicketCrudController::class);
    Route::crud('category', CategoryCrudController::class);
    Route::get('/edit-account-info', fn () => redirect()->route('profile.edit'))->name('backpack.account.info');
});
Route::middleware('web')->get('/admin/login', fn () => redirect()->route('login'))->name('backpack.auth.login');
// The default theme expects a logout URL. GET only displays the profile; logout stays POST + CSRF.
Route::middleware(['web', 'auth'])->get('/admin/logout', fn () => redirect()->route('profile.edit'))->name('backpack.auth.logout');
