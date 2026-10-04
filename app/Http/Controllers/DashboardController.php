<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/** Главная страница личного кабинета со статистикой собственных заявок. */
class DashboardController extends Controller
{
    /** __invoke позволяет назначить контроллер маршруту без указания отдельного метода. */
    public function __invoke(Request $request)
    {
        // Группируем в БД: не нужно загружать все заявки в память, чтобы посчитать их.
        $counts = $request->user()->tickets()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        // SoftDeletes автоматически исключает удалённые заявки и из счётчиков, и из списка.
        $tickets = $request->user()->tickets()->with('category')->latest()->limit(5)->get();

        return view('dashboard', compact('counts', 'tickets'));
    }
}
