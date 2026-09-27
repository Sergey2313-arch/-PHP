<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use CrudTrait, HasFactory;

    protected $fillable = ['name', 'slug'];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
