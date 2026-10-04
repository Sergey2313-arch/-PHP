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

/** CRUD категорий с запретом удаления категорий, используемых в заявках. */
class CategoryCrudController extends CrudController
{
    use CreateOperation, DeleteOperation, ListOperation, ShowOperation, UpdateOperation;

    /** Указывает Backpack модель, адрес и русские названия сущности. */
    public function setup(): void
    {
        $this->crud->setModel(Category::class);
        $this->crud->setRoute(backpack_url('category'));
        $this->crud->setEntityNameStrings('категорию', 'Категории');
    }

    /** Настраивает столбцы списка; данные Backpack получает через модель Category. */
    protected function setupListOperation(): void
    {
        $this->crud->addColumns([
            ['name' => 'name', 'label' => 'Название', 'type' => 'text'],
            ['name' => 'slug', 'label' => 'Код', 'type' => 'text'],
            ['name' => 'created_at', 'label' => 'Создана', 'type' => 'datetime'],
        ]);
    }

    /** В карточке выводим только заранее выбранные поля. */
    protected function setupShowOperation(): void
    {
        $this->crud->set('show.setFromDb', false);
        $this->setupListOperation();
    }

    /** Поля формы и правила CategoryRequest используются при создании и изменении. */
    protected function setupCreateOperation(): void
    {
        $this->crud->setValidation(CategoryRequest::class);
        $this->crud->addFields([
            ['name' => 'name', 'label' => 'Название', 'type' => 'text'],
            ['name' => 'slug', 'label' => 'Код (slug)', 'type' => 'text', 'hint' => 'Латинские строчные буквы, цифры и дефисы. Например: software'],
        ]);
    }

    /** Для изменения категории достаточно той же формы, что и для создания. */
    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();
    }

    /** Удаляет только свободную категорию; связанные заявки должны сохранить свой category_id. */
    public function destroy($id)
    {
        $this->crud->hasAccessOrFail('delete');
        $category = Category::findOrFail($id);
        // Учитываем даже мягко удалённые заявки: их строки остаются в таблице tickets.
        if ($category->tickets()->withTrashed()->exists()) {
            return response()->json(['message' => 'Категория используется в заявках, включая удалённые.'], 422);
        }
        try {
            $category->delete();
        } catch (QueryException $exception) {
            // Внешний ключ защищает и от заявки, созданной одновременно с удалением категории.
            if ($exception->getCode() === '23000') {
                return response()->json(['message' => 'Категория используется в заявках.'], 422);
            }
            throw $exception;
        }

        return response()->json(1);
    }
}
