<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeders_are_repeatable_and_relationships_work(): void
    {
        $this->seed();
        $this->seed();
        $this->assertDatabaseCount('users', 4);
        $this->assertDatabaseCount('categories', 5);
        $this->assertDatabaseCount('tickets', 18);
        $this->assertTrue(User::where('email', 'admin@example.test')->firstOrFail()->isAdmin());
        $ticket = Ticket::firstOrFail();
        $this->assertTrue($ticket->user->tickets->contains($ticket));
        $this->assertTrue($ticket->category->tickets->contains($ticket));
    }
}
