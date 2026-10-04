{{-- Эта форма отправляет только статус; описание и автор заявки не меняются. --}}
<form action="{{ route('admin.ticket.status', $entry) }}" method="post" class="d-flex flex-column gap-2" style="min-width: 190px" aria-label="Изменить статус заявки {{ $entry->id }}">
    @csrf
    @method('PATCH')
    @if(request()->query('status'))
        <input type="hidden" name="return_status" value="{{ request()->query('status') }}">
    @endif
    <label for="ticket-status-{{ $entry->id }}" class="visually-hidden">Статус заявки {{ $entry->id }}</label>
    <select id="ticket-status-{{ $entry->id }}" name="status" class="form-select form-select-sm" required>
        @foreach(\App\Models\Ticket::STATUSES as $value => $label)
            <option value="{{ $value }}" @selected($entry->status === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-sm btn-outline-primary">Сохранить</button>
</form>
