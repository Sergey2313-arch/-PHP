<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Заявка: данные, связи с автором и категорией, допустимые статусы. */
class Ticket extends Model
{
    use CrudTrait, HasFactory, SoftDeletes;

    // В базе храним постоянные коды, а в формах и таблицах показываем русские подписи.
    public const STATUSES = ['new' => 'Новая', 'under_review' => 'На рассмотрении', 'in_progress' => 'В работе', 'resolved' => 'Решена', 'closed' => 'Закрыта'];

    public const PRIORITIES = ['low' => 'Низкий', 'medium' => 'Средний', 'high' => 'Высокий', 'critical' => 'Критический'];

    public const STATUS_COLORS = ['new' => 'primary', 'under_review' => 'info', 'in_progress' => 'warning', 'resolved' => 'success', 'closed' => 'secondary'];

    public const PRIORITY_COLORS = ['low' => 'secondary', 'medium' => 'info', 'high' => 'warning', 'critical' => 'danger'];

    // status и user_id нельзя массово присвоить из пользовательской формы.
    // Автор задаётся через связь user()->tickets(), статус меняет только администратор.
    protected $fillable = ['category_id', 'title', 'description', 'priority'];

    protected $attributes = ['status' => 'new', 'priority' => 'medium'];

    /** Одна заявка принадлежит одному пользователю. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** По category_id получаем название категории без ручного JOIN. */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
