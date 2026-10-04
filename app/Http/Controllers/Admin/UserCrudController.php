<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

/** Справочник пользователей в админке: только просмотр, без изменения ролей и паролей. */
class UserCrudController extends CrudController
{
    // Подключены только операции списка и карточки; Create/Update/Delete здесь отсутствуют.
    use ListOperation, ShowOperation;

    /** Связывает CRUD с моделью User и адресом /admin/user. */
    public function setup(): void
    {
        $this->crud->setModel(User::class);
        $this->crud->setRoute(backpack_url('user'));
        $this->crud->setEntityNameStrings('пользователя', 'Пользователи');
    }

    /** Явно перечисляет видимые поля: хеш пароля и remember_token не выводятся. */
    protected function setupListOperation(): void
    {
        $this->crud->addColumns([
            ['name' => 'id', 'label' => 'ID', 'type' => 'number'],
            ['name' => 'name', 'label' => 'Имя', 'type' => 'text'],
            ['name' => 'email', 'label' => 'Email', 'type' => 'email'],
            ['name' => 'role', 'label' => 'Роль', 'type' => 'select_from_array', 'options' => ['user' => 'Пользователь', 'admin' => 'Администратор']],
            ['name' => 'created_at', 'label' => 'Дата регистрации', 'type' => 'datetime'],
        ]);
    }

    /** Отключает автоматический вывод всех полей БД в карточке. */
    protected function setupShowOperation(): void
    {
        $this->crud->set('show.setFromDb', false);
        $this->setupListOperation();
    }
}
