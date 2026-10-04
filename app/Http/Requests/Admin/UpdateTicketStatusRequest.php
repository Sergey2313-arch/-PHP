<?php

namespace App\Http\Requests\Admin;

use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Проверяет отдельную форму смены статуса без повторной отправки описания заявки. */
class UpdateTicketStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Проверка остаётся на сервере, даже если кто-то отправит PATCH вручную.
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(array_keys(Ticket::STATUSES))],
            // Возвращаемся к выбранному фильтру, принимая только известный код статуса.
            'return_status' => ['nullable', 'string', Rule::in(array_keys(Ticket::STATUSES))],
        ];
    }
}
