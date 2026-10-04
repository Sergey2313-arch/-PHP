@extends('crud::list')

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <h2 class="h5 mb-2">Заявки по статусам</h2>
            <p class="text-secondary mb-3">В столбце «Статус заявки» выберите состояние и нажмите «Сохранить».</p>
            <nav class="d-flex flex-wrap gap-2" aria-label="Фильтр заявок по статусу">
                <a href="{{ backpack_url('ticket') }}" class="btn {{ !$statusFilter ? 'btn-primary' : 'btn-outline-primary' }}" @if(!$statusFilter) aria-current="page" @endif>
                    Все заявки <span class="badge bg-white text-dark ms-2">{{ $statusCounts->sum() }}</span>
                </a>
                @foreach(\App\Models\Ticket::STATUSES as $value => $label)
                    <a href="{{ backpack_url('ticket').'?status='.$value }}" class="btn {{ $statusFilter === $value ? 'btn-primary' : 'btn-outline-primary' }}" @if($statusFilter === $value) aria-current="page" @endif>
                        {{ $label }} <span class="badge bg-white text-dark ms-2">{{ $statusCounts[$value] ?? 0 }}</span>
                    </a>
                @endforeach
            </nav>
        </div>
    </div>
    @parent
@endsection
