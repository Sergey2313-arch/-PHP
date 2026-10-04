<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateTicketStatusRequest;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

/** Однодейственный контроллер: меняет только статус из формы в административной таблице. */
class TicketStatusController extends Controller
{
    public function __invoke(UpdateTicketStatusRequest $request, Ticket $ticket): RedirectResponse
    {
        // Laravel находит Ticket по параметру маршрута; отсутствующая/удалённая заявка даст 404.
        // До входа в метод Form Request уже проверил роль администратора и допустимость статуса.
        $previousStatus = $ticket->status;
        $ticket->status = $request->validated('status');
        $ticket->save();

        Log::info('Ticket status updated by administrator', [
            'ticket_id' => $ticket->id,
            'admin_id' => $request->user()->id,
            'from' => $previousStatus,
            'to' => $ticket->status,
        ]);

        \Alert::success('Статус заявки #'.$ticket->id.': '.Ticket::STATUSES[$ticket->status])->flash();

        // Строим локальный адрес сами: произвольный URL перенаправления из формы не принимаем.
        $filter = $request->validated('return_status');

        return redirect(backpack_url('ticket').($filter ? '?status='.$filter : ''));
    }
}
