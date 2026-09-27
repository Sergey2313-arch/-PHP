<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\TicketRequest;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Illuminate\Support\Facades\Log;

class TicketCrudController extends CrudController
{
    use DeleteOperation, ListOperation, ShowOperation, UpdateOperation;

    public function setup(): void
    {
        $this->crud->setModel(Ticket::class);
        $this->crud->setRoute(backpack_url('ticket'));
        $this->crud->setEntityNameStrings('заявку', 'Заявки');
        $this->crud->with(['user', 'category']);
        $this->crud->orderBy('created_at', 'desc');
    }

    protected function setupListOperation(): void
    {
        $this->crud->disableResponsiveTable();
        $this->crud->addColumns([
            ['name' => 'id', 'label' => 'ID', 'type' => 'number'],
            ['name' => 'title', 'label' => 'Название', 'type' => 'text'],
            ['name' => 'user_id', 'label' => 'Автор', 'type' => 'select', 'entity' => 'user', 'model' => User::class, 'attribute' => 'email'],
            ['name' => 'category_id', 'label' => 'Категория', 'type' => 'select', 'entity' => 'category', 'model' => Category::class, 'attribute' => 'name'],
            ['name' => 'status', 'label' => 'Статус', 'type' => 'select_from_array', 'options' => Ticket::STATUSES],
            ['name' => 'priority', 'label' => 'Приоритет', 'type' => 'select_from_array', 'options' => Ticket::PRIORITIES],
            ['name' => 'created_at', 'label' => 'Создана', 'type' => 'datetime'],
        ]);
    }

    protected function setupShowOperation(): void
    {
        $this->crud->set('show.setFromDb', false);
        $this->setupListOperation();
        $this->crud->addColumn(['name' => 'description', 'label' => 'Описание', 'type' => 'textarea']);
    }

    protected function setupUpdateOperation(): void
    {
        $this->crud->setValidation(TicketRequest::class);
        $this->crud->addFields([
            ['name' => 'title', 'label' => 'Название', 'type' => 'text'],
            ['name' => 'description', 'label' => 'Описание', 'type' => 'textarea'],
            ['name' => 'category_id', 'label' => 'Категория', 'type' => 'select', 'entity' => 'category', 'model' => Category::class, 'attribute' => 'name'],
            ['name' => 'status', 'label' => 'Статус', 'type' => 'select_from_array', 'options' => Ticket::STATUSES],
            ['name' => 'priority', 'label' => 'Приоритет', 'type' => 'select_from_array', 'options' => Ticket::PRIORITIES],
        ]);
    }

    public function update()
    {
        $this->crud->hasAccessOrFail('update');
        $request = $this->crud->validateRequest();
        $ticket = Ticket::findOrFail($this->crud->getCurrentEntryId());
        $ticket->fill($request->safe()->except('status'));
        $ticket->status = $request->validated('status');
        $ticket->save();
        Log::info('Ticket updated by administrator', ['ticket_id' => $ticket->id, 'admin_id' => auth()->id()]);
        \Alert::success('Заявка обновлена.')->flash();
        $this->data['entry'] = $this->crud->entry = $ticket;
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($ticket->getKey());
    }
}
