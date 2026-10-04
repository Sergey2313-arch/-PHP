<?php

namespace App\Http\Requests;

/** Повторно использует правила создания, но требует права на изменение выбранной заявки. */
class UpdateTicketRequest extends StoreTicketRequest
{
    public function authorize(): bool
    {
        // can() обращается к TicketPolicy, зарегистрированной в AppServiceProvider.
        return $this->user()->can('update', $this->route('ticket'));
    }
}
