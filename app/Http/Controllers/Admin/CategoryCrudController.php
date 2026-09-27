<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Illuminate\Database\QueryException;

class CategoryCrudController extends CrudController
{
    use CreateOperation, DeleteOperation, ListOperation, ShowOperation, UpdateOperation;

    public function setup(): void
    {
        $this->crud->setModel(Category::class);
        $this->crud->setRoute(backpack_url('category'));
        $this->crud->setEntityNameStrings('категорию', 'Категории');
    }

    protected function setupListOperation(): void
    {
        $this->crud->addColumns([
            ['name' => 'name', 'label' => 'Название', 'type' => 'text'],
            ['name' => 'slug', 'label' => 'Код', 'type' => 'text'],
            ['name' => 'created_at', 'label' => 'Создана', 'type' => 'datetime'],
        ]);
    }

    protected function setupShowOperation(): void
    {
        $this->crud->set('show.setFromDb', false);
        $this->setupListOperation();
    }

    protected function setupCreateOperation(): void
    {
        $this->crud->setValidation(CategoryRequest::class);
        $this->crud->addFields([
            ['name' => 'name', 'label' => 'Название', 'type' => 'text'],
            ['name' => 'slug', 'label' => 'Код (slug)', 'type' => 'text', 'hint' => 'Латинские строчные буквы, цифры и дефисы. Например: software'],
        ]);
    }

    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();
    }

    public function destroy($id)
    {
        $this->crud->hasAccessOrFail('delete');
        $category = Category::findOrFail($id);
        if ($category->tickets()->withTrashed()->exists()) {
            return response()->json(['message' => 'Категория используется в заявках, включая удалённые.'], 422);
        }
        try {
            $category->delete();
        } catch (QueryException $exception) {
            // The foreign key also protects against a ticket created concurrently.
            if ($exception->getCode() === '23000') {
                return response()->json(['message' => 'Категория используется в заявках.'], 422);
            }
            throw $exception;
        }

        return response()->json(1);
    }
}
