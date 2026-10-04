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
use Illuminate\Validation\Rule;

/** Настраивает список, карточку и полную форму редактирования заявок в Backpack. */
class TicketCrudController extends CrudController
{
    use DeleteOperation, ListOperation, ShowOperation, UpdateOperation;

    /** Общие настройки для всех операций этого CRUD. */
    public function setup(): void
    {
        $this->crud->setModel(Ticket::class);
        $this->crud->setRoute(backpack_url('ticket'));
        $this->crud->setEntityNameStrings('заявку', 'Заявки');
        // Заранее загружаем связи, чтобы не выполнять отдельные запросы для каждой строки.
        $this->crud->with(['user', 'category']);
        $this->crud->orderBy('created_at', 'desc');
    }

    /** Фильтр применяется и к странице, и к AJAX-запросу таблицы /ticket/search. */
    protected function setupListOperation(): void
    {
        $this->crud->disableResponsiveTable();
        $this->crud->setListView('admin.tickets.list');

        $filter = validator(['status' => request()->query('status')], [
            'status' => ['nullable', 'string', Rule::in(array_keys(Ticket::STATUSES))],
        ])->validate()['status'] ?? null;

        $this->data['statusFilter'] = $filter;
        // Счётчики относятся ко всем активным заявкам, независимо от выбранной вкладки.
        $this->data['statusCounts'] = Ticket::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        if ($filter) {
            $this->crud->addClause('where', 'status', $filter);
        }

        $this->setupTicketColumns();
    }

    /** Список и карточка используют одинаковые подписи и форму изменения статуса. */
    protected function setupTicketColumns(): void
    {
        $this->crud->addColumns([
            ['name' => 'id', 'label' => 'ID', 'type' => 'number'],
            ['name' => 'title', 'label' => 'Название', 'type' => 'text'],
            ['name' => 'user_id', 'label' => 'Автор', 'type' => 'select', 'entity' => 'user', 'model' => User::class, 'attribute' => 'email'],
            ['name' => 'category_id', 'label' => 'Категория', 'type' => 'select', 'entity' => 'category', 'model' => Category::class, 'attribute' => 'name'],
            ['name' => 'status', 'label' => 'Статус заявки', 'type' => 'view', 'view' => 'admin.tickets.status-form', 'orderable' => false, 'searchLogic' => false],
            ['name' => 'priority', 'label' => 'Приоритет', 'type' => 'select_from_array', 'options' => Ticket::PRIORITIES],
            ['name' => 'created_at', 'label' => 'Создана', 'type' => 'datetime'],
        ]);
    }

    /** Карточка показывает те же данные и полное описание обращения. */
    protected function setupShowOperation(): void
    {
        $this->crud->set('show.setFromDb', false);
        $this->setupTicketColumns();
        $this->crud->addColumn(['name' => 'description', 'label' => 'Описание', 'type' => 'textarea']);
    }

    /** Полная форма: администратор может изменить и статус, и содержание заявки. */
    protected function setupUpdateOperation(): void
    {
        $this->crud->setValidation(TicketRequest::class);
        $this->crud->addFields([
            ['name' => 'status', 'label' => 'Статус заявки', 'type' => 'select_from_array', 'options' => Ticket::STATUSES, 'allows_null' => false, 'hint' => 'Выберите текущее состояние обращения и сохраните изменения.'],
            ['name' => 'title', 'label' => 'Название', 'type' => 'text'],
            ['name' => 'description', 'label' => 'Описание', 'type' => 'textarea'],
            ['name' => 'category_id', 'label' => 'Категория', 'type' => 'select', 'entity' => 'category', 'model' => Category::class, 'attribute' => 'name'],
            ['name' => 'priority', 'label' => 'Приоритет', 'type' => 'select_from_array', 'options' => Ticket::PRIORITIES],
        ]);
    }

    /** Сохраняет полную форму после проверки прав и правил административного TicketRequest. */
    public function update()
    {
        $this->crud->hasAccessOrFail('update');
        $request = $this->crud->validateRequest();
        $ticket = Ticket::findOrFail($this->crud->getCurrentEntryId());
        $ticket->fill($request->safe()->except('status'));
        // status отсутствует в fillable: администратор присваивает его явно после проверки запроса.
        $ticket->status = $request->validated('status');
        $ticket->save();
        Log::info('Ticket updated by administrator', ['ticket_id' => $ticket->id, 'admin_id' => auth()->id()]);
        \Alert::success('Заявка обновлена.')->flash();
        $this->data['entry'] = $this->crud->entry = $ticket;
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($ticket->getKey());
    }
}
