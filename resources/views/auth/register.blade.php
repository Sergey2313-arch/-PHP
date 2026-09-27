@extends('layouts.app')
@section('title', 'Регистрация')
@section('content')
<div class="auth-heading"><p class="eyebrow">Начало работы</p><h1>Создайте аккаунт</h1><p class="text-secondary">Расскажите о проблеме — мы поможем её решить.</p></div>
<div class="card auth-card"><div class="card-body">
    <form method="post" action="{{ route('register') }}">@csrf
        <div class="mb-3"><label for="name" class="form-label">Имя</label><input id="name" class="form-control" name="name" value="{{ old('name') }}" maxlength="100" autocomplete="name" required autofocus></div>
        <div class="mb-3"><label for="email" class="form-label">Email</label><input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}" maxlength="255" autocomplete="email" required></div>
        <div class="mb-3"><label for="password" class="form-label">Пароль</label><input id="password" class="form-control" type="password" name="password" minlength="8" autocomplete="new-password" required><div class="form-hint mt-1">Минимум 8 символов.</div></div>
        <div class="mb-4"><label for="password_confirmation" class="form-label">Повторите пароль</label><input id="password_confirmation" class="form-control" type="password" name="password_confirmation" minlength="8" autocomplete="new-password" required></div>
        <button class="btn btn-primary w-100" type="submit">Зарегистрироваться</button>
    </form>
    <p class="mb-0 mt-4 text-center small">Уже есть аккаунт? <a href="{{ route('login') }}">Войти</a></p>
</div></div>
@endsection
