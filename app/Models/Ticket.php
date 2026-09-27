<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use CrudTrait, HasFactory, SoftDeletes;

    public const STATUSES = ['new' => 'Новая', 'in_progress' => 'В работе', 'resolved' => 'Решена', 'closed' => 'Закрыта'];

    public const PRIORITIES = ['low' => 'Низкий', 'medium' => 'Средний', 'high' => 'Высокий', 'critical' => 'Критический'];

    public const STATUS_COLORS = ['new' => 'primary', 'in_progress' => 'warning', 'resolved' => 'success', 'closed' => 'secondary'];

    public const PRIORITY_COLORS = ['low' => 'secondary', 'medium' => 'info', 'high' => 'warning', 'critical' => 'danger'];

    protected $fillable = ['category_id', 'title', 'description', 'priority'];

    protected $attributes = ['status' => 'new', 'priority' => 'medium'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
