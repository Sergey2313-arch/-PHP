@extends('layouts.app')
@section('title', 'Главная')
@section('content')
<div class="page-header"><div><p class="eyebrow">Личный кабинет</p><h1>Здравствуйте, {{ auth()->user()->name }}</h1><p class="text-secondary mb-0">Все обращения в поддержку — в одном месте.</p></div><a class="btn btn-primary" href="{{ route('tickets.create') }}">+ Создать заявку</a></div>
<div class="row row-cols-2 row-cols-lg-5 g-3 mb-4">
    <div class="col"><div class="card h-100"><div class="card-body"><p class="text-secondary small mb-2">Всего заявок</p><div class="stat-value">{{ $counts->sum() }}</div></div></div></div>
    @foreach(\App\Models\Ticket::STATUSES as $status => $label)
    <div class="col"><div class="card h-100"><div class="card-body"><p class="text-secondary small mb-2">{{ $label }}</p><div class="stat-value">{{ $counts[$status] ?? 0 }}</div></div></div></div>
    @endforeach
</div>
<div class="card"><div class="card-header d-flex justify-content-between align-items-center"><h2 class="h5 mb-0">Последние заявки</h2><a class="small" href="{{ route('tickets.index') }}">Все заявки →</a></div>@include('tickets.table')</div>
@endsection
