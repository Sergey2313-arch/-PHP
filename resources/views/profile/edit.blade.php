@extends('layouts.app')
@section('title', 'Профиль')
@section('content')
<div class="page-header"><div><p class="eyebrow">Настройки аккаунта</p><h1>Мой профиль</h1><p class="text-secondary mb-0">Личные данные и безопасность.</p></div></div>
<div class="row g-4"><div class="col-lg-7"><div class="card"><div class="card-header"><h2 class="h5 mb-0">Личные данные</h2></div><div class="card-body">
    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data">@csrf @method('PATCH')
        @if($user->avatar)<img class="avatar mb-3" src="{{ asset('storage/'.$user->avatar) }}" alt="Ваш аватар">@endif
        <div class="mb-3"><label for="avatar" class="form-label">Аватар</label><input type="file" id="avatar" name="avatar" class="form-control" accept="image/jpeg,image/png,image/webp"><div class="form-hint mt-1">JPG, PNG или WebP, до 2 МБ. Новая фотография заменит предыдущую.</div></div>
        <div class="mb-3"><label for="name" class="form-label">Имя</label><input id="name" name="name" class="form-control" value="{{ old('name', $user->name) }}" maxlength="100" required autocomplete="name"></div>
        <div class="mb-4"><label for="email" class="form-label">Email</label><input id="email" name="email" type="email" class="form-control" value="{{ old('email', $user->email) }}" maxlength="255" required autocomplete="email"></div>
        <button class="btn btn-primary" type="submit">Сохранить профиль</button>
    </form>
</div></div></div><div class="col-lg-5"><div class="card"><div class="card-header"><h2 class="h5 mb-0">Изменить пароль</h2></div><div class="card-body">
    <form method="post" action="{{ route('profile.password') }}">@csrf @method('PUT')
        <div class="mb-3"><label for="current_password" class="form-label">Текущий пароль</label><input id="current_password" name="current_password" type="password" class="form-control" required autocomplete="current-password"></div>
        <div class="mb-3"><label for="password" class="form-label">Новый пароль</label><input id="password" name="password" type="password" class="form-control" required minlength="8" autocomplete="new-password"><div class="form-hint mt-1">Минимум 8 символов.</div></div>
        <div class="mb-4"><label for="password_confirmation" class="form-label">Повторите новый пароль</label><input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required minlength="8" autocomplete="new-password"></div>
        <button class="btn btn-outline-primary" type="submit">Изменить пароль</button>
    </form>
</div></div></div></div>
@endsection
