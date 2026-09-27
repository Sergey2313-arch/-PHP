@extends('layouts.app')
@section('title', 'Доступ запрещён')
@section('content')
<div class="card"><div class="empty-state"><p class="eyebrow">Ошибка 403</p><h1>Доступ запрещён</h1><p class="text-secondary">У вас нет прав для просмотра этой страницы.</p><a class="btn btn-primary" href="{{ url('/') }}">На главную</a></div></div>
@endsection
