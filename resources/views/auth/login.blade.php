@extends('layouts.app')
@section('title', 'Вход')
@section('content')
<div class="auth-heading"><p class="eyebrow">Техническая поддержка</p><h1>Рады видеть вас снова</h1><p class="text-secondary">Войдите, чтобы следить за своими заявками.</p></div>
<div class="card auth-card"><div class="card-body">
    <form method="post" action="{{ route('login') }}">@csrf
        <div class="mb-3"><label for="email" class="form-label">Email</label><input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus></div>
        <div class="mb-3"><label for="password" class="form-label">Пароль</label><input id="password" class="form-control" type="password" name="password" autocomplete="current-password" required></div>
        <div class="form-check mb-4"><input class="form-check-input" type="checkbox" name="remember" value="1" id="remember"><label class="form-check-label" for="remember">Запомнить меня</label></div>
        <button class="btn btn-primary w-100" type="submit">Войти</button>
    </form>
    <p class="mb-0 mt-4 text-center small">Нет аккаунта? <a href="{{ route('register') }}">Зарегистрироваться</a></p>
</div></div>
@endsection
