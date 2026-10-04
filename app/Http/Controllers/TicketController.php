<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Models\Category;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/** Пользовательский CRUD: каждый работает только со своими заявками. */
class TicketController extends Controller
{
    /** GET /tickets: отбираем заявки владельца, загружаем категории и разбиваем список на страницы. */
    public function index(Request $request)
    {
        $tickets = $request->user()->tickets()->with('category')->latest()->paginate(10);

        return view('tickets.index', compact('tickets'));
    }

    /** GET /tickets/create: готовим данные для формы, но ещё ничего не сохраняем. */
    public function create()
    {
        return view('tickets.create', ['ticket' => new Ticket, 'categories' => Category::orderBy('name')->get()]);
    }

    /** POST /tickets: Form Request проверяется Laravel до вызова этого метода. */
    public function store(StoreTicketRequest $request)
    {
        // Создание через связь задаёт user_id из сессии, а не из отправленной формы.
        $ticket = $request->user()->tickets()->create($request->validated());

        return redirect()->route('tickets.show', $ticket)->with('success', 'Заявка создана.');
    }

    /** GET /tickets/{ticket}: Route Model Binding находит модель, Policy проверяет владельца. */
    public function show(Ticket $ticket)
    {
        Gate::authorize('view', $ticket);

        return view('tickets.show', ['ticket' => $ticket->load('category', 'user')]);
    }

    /** GET /tickets/{ticket}/edit: проверяем доступ перед показом формы изменения. */
    public function edit(Ticket $ticket)
    {
        Gate::authorize('update', $ticket);

        return view('tickets.edit', ['ticket' => $ticket, 'categories' => Category::orderBy('name')->get()]);
    }

    /** PUT/PATCH /tickets/{ticket}: право update проверено в authorize() запроса. */
    public function update(UpdateTicketRequest $request, Ticket $ticket)
    {
        $ticket->update($request->validated());

        return redirect()->route('tickets.show', $ticket)->with('success', 'Заявка обновлена.');
    }

    /** DELETE /tickets/{ticket}: SoftDeletes запишет deleted_at вместо удаления строки. */
    public function destroy(Ticket $ticket)
    {
        Gate::authorize('delete', $ticket);
        $ticket->delete();

        return redirect()->route('tickets.index')->with('success', 'Заявка удалена.');
    }
}
