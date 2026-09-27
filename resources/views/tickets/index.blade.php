@extends('layouts.app')
@section('title', 'Мои заявки')
@section('content')
<div class="page-header"><div><p class="eyebrow">Техническая поддержка</p><h1>Мои заявки</h1><p class="text-secondary mb-0">История обращений и текущий статус решения.</p></div><a class="btn btn-primary" href="{{ route('tickets.create') }}">+ Создать заявку</a></div>
<div class="card">@include('tickets.table')</div>
<div class="mt-4">{{ $tickets->links() }}</div>
@endsection
