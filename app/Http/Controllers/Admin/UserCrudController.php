<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

class UserCrudController extends CrudController
{
    use ListOperation, ShowOperation;

    public function setup(): void
    {
        $this->crud->setModel(User::class);
        $this->crud->setRoute(backpack_url('user'));
        $this->crud->setEntityNameStrings('пользователя', 'Пользователи');
    }

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

    protected function setupShowOperation(): void
    {
        $this->crud->set('show.setFromDb', false);
        $this->setupListOperation();
    }
}
