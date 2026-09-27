<?php

namespace App\Http\Requests;

class UpdateTicketRequest extends StoreTicketRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('ticket'));
    }
}
