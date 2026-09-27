@if(backpack_user())
<li class="nav-item"><a class="nav-link" href="{{ route('profile.edit') }}"><img src="{{ backpack_user()->avatarUrl() }}" alt="" class="avatar avatar-sm rounded-circle me-2">{{ backpack_user()->name }}</a></li>
<li class="nav-separator"></li>
@endif
