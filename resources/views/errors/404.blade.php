@extends('layouts.app')
@section('title', 'Страница не найдена')
@section('content')
<div class="card"><div class="empty-state"><p class="eyebrow">Ошибка 404</p><h1>Страница не найдена</h1><p class="text-secondary">Возможно, адрес изменился или заявка была удалена.</p><a class="btn btn-primary" href="{{ url('/') }}">На главную</a></div></div>
@endsection
