<?php

namespace App\Http\Requests;

use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:20000'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'priority' => ['sometimes', 'required', Rule::in(array_keys(Ticket::PRIORITIES))],
            'status' => ['prohibited'],
            'user_id' => ['prohibited'],
        ];
    }
}
