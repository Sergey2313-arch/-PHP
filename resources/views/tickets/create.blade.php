@extends('layouts.app')
@section('title', 'Новая заявка')
@section('content')
<div class="page-header"><div><p class="eyebrow">Новое обращение</p><h1>Создать заявку</h1><p class="text-secondary mb-0">Подробное описание поможет быстрее решить проблему.</p></div></div>
<div class="row"><div class="col-lg-9"><div class="card"><div class="card-body"><form method="post" action="{{ route('tickets.store') }}">@include('tickets.form')</form></div></div></div></div>
@endsection
