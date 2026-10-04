<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

/** Проверяет права на конкретную заявку в пользовательской части приложения. */
class TicketPolicy
{
    // Даже администратор работает с чужими заявками через Backpack, а не через /tickets.
    public function view(User $user, Ticket $ticket): bool
    {
        return $user->id === $ticket->user_id;
    }

    /** Изменять заявку может тот же пользователь, которому разрешён её просмотр. */
    public function update(User $user, Ticket $ticket): bool
    {
        return $this->view($user, $ticket);
    }

    /** Удаление подчиняется тому же правилу владельца. */
    public function delete(User $user, Ticket $ticket): bool
    {
        return $this->view($user, $ticket);
    }
}
