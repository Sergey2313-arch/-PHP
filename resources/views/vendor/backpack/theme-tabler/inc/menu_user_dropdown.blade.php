<div class="d-flex align-items-center gap-3 px-2">
    <a href="{{ route('profile.edit') }}" class="text-reset">{{ backpack_user()->name }}</a>
    <form method="post" action="{{ route('logout') }}">@csrf<button type="submit" class="btn btn-outline-secondary btn-sm">Выйти</button></form>
</div>
