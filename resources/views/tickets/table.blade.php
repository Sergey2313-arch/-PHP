@if($tickets->isEmpty())
    <div class="empty-state"><h2 class="h5">Пока нет заявок</h2><p class="text-secondary">Если нужна помощь, опишите проблему в новой заявке.</p><a href="{{ route('tickets.create') }}" class="btn btn-primary">Создать первую заявку</a></div>
@else
<div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>ID</th><th>Название</th><th>Категория</th><th>Приоритет</th><th>Статус</th><th>Создана</th></tr></thead>
    <tbody>@foreach($tickets as $ticket)<tr>
        <td class="text-secondary">#{{ $ticket->id }}</td>
        <td class="ticket-title"><a class="ticket-link" href="{{ route('tickets.show', $ticket) }}">{{ $ticket->title }}</a></td>
        <td class="small">{{ $ticket->category->name }}</td>
        <td><span class="badge text-bg-{{ \App\Models\Ticket::PRIORITY_COLORS[$ticket->priority] }}">{{ \App\Models\Ticket::PRIORITIES[$ticket->priority] }}</span></td>
        <td><span class="badge text-bg-{{ \App\Models\Ticket::STATUS_COLORS[$ticket->status] }}">{{ \App\Models\Ticket::STATUSES[$ticket->status] }}</span></td>
        <td class="text-secondary small text-nowrap">{{ $ticket->created_at->format('d.m.Y H:i') }}</td>
    </tr>@endforeach</tbody>
</table></div>
@endif
