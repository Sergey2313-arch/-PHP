@props(['ticket'])
<span class="badge text-bg-{{ \App\Models\Ticket::PRIORITY_COLORS[$ticket->priority] }}">{{ \App\Models\Ticket::PRIORITIES[$ticket->priority] }}</span>
<span class="badge text-bg-{{ \App\Models\Ticket::STATUS_COLORS[$ticket->status] }}">{{ \App\Models\Ticket::STATUSES[$ticket->status] }}</span>
