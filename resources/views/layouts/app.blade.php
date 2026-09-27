<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Главная') · IT Service Desk</title>
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ url('/') }}"><span class="brand-mark">IT</span> Service Desk</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navigation" aria-controls="navigation" aria-expanded="false" aria-label="Открыть меню"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="navigation">
            <div class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                @auth
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Главная</a>
                    <a class="nav-link {{ request()->routeIs('tickets.index', 'tickets.show', 'tickets.edit') ? 'active' : '' }}" href="{{ route('tickets.index') }}">Мои заявки</a>
                    <a class="nav-link {{ request()->routeIs('tickets.create') ? 'active' : '' }}" href="{{ route('tickets.create') }}">Создать заявку</a>
                    <a class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.edit') }}">Профиль</a>
                    @if(auth()->user()->isAdmin())<a class="nav-link" href="{{ url('/admin') }}">Админ-панель</a>@endif
                    <form method="post" action="{{ route('logout') }}">@csrf<button class="btn btn-outline-light btn-sm" type="submit">Выйти</button></form>
                @else
                    <a class="nav-link" href="{{ route('login') }}">Вход</a>
                    <a class="btn btn-primary btn-sm" href="{{ route('register') }}">Регистрация</a>
                @endauth
            </div>
        </div>
    </div>
</nav>
<main class="container py-4 py-lg-5">
    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())
        <div class="alert alert-danger" role="alert"><strong>Проверьте заполнение формы.</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    @yield('content')
</main>
<footer class="container pb-4 text-secondary small d-flex justify-content-between flex-wrap gap-2"><span>IT Service Desk</span><span>Служба технической поддержки</span></footer>
<script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
