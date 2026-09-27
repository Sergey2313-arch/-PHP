@extends('layouts.app')
@section('title', 'Редактирование заявки')
@section('content')
<div class="page-header"><div><p class="eyebrow">Заявка #{{ $ticket->id }}</p><h1>Редактирование заявки</h1></div></div>
<div class="row"><div class="col-lg-9"><div class="card"><div class="card-body"><form method="post" action="{{ route('tickets.update', $ticket) }}">@method('PUT') @include('tickets.form')</form></div></div></div></div>
@endsection
