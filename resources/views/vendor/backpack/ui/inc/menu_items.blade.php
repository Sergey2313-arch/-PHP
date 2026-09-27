<x-backpack::menu-item title="Заявки" icon="la la-ticket" :link="backpack_url('ticket')" />
<x-backpack::menu-item title="Пользователи" icon="la la-users" :link="backpack_url('user')" />
<x-backpack::menu-item title="Категории" icon="la la-folder" :link="backpack_url('category')" />
<x-backpack::menu-item title="Личный кабинет" icon="la la-home" :link="route('dashboard')" />
<li class="nav-item"><form action="{{ route('logout') }}" method="post" class="px-3 py-2">@csrf<button class="btn btn-outline-secondary btn-sm" type="submit">Выйти</button></form></li>
