<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Models\Category;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $tickets = $request->user()->tickets()->with('category')->latest()->paginate(10);

        return view('tickets.index', compact('tickets'));
    }

    public function create()
    {
        return view('tickets.create', ['ticket' => new Ticket, 'categories' => Category::orderBy('name')->get()]);
    }

    public function store(StoreTicketRequest $request)
    {
        $ticket = $request->user()->tickets()->create($request->validated());

        return redirect()->route('tickets.show', $ticket)->with('success', 'Заявка создана.');
    }

    public function show(Ticket $ticket)
    {
        Gate::authorize('view', $ticket);

        return view('tickets.show', ['ticket' => $ticket->load('category', 'user')]);
    }

    public function edit(Ticket $ticket)
    {
        Gate::authorize('update', $ticket);

        return view('tickets.edit', ['ticket' => $ticket, 'categories' => Category::orderBy('name')->get()]);
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket)
    {
        $ticket->update($request->validated());

        return redirect()->route('tickets.show', $ticket)->with('success', 'Заявка обновлена.');
    }

    public function destroy(Ticket $ticket)
    {
        Gate::authorize('delete', $ticket);
        $ticket->delete();

        return redirect()->route('tickets.index')->with('success', 'Заявка удалена.');
    }
}
