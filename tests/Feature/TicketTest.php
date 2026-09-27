<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    private function data(): array
    {
        return ['title' => 'Не работает принтер', 'description' => 'Не печатает после перезапуска.', 'category_id' => Category::factory()->create()->id, 'priority' => 'high'];
    }

    public function test_user_can_create_a_ticket_and_defaults_are_applied(): void
    {
        $user = User::factory()->create();
        $data = $this->data();
        unset($data['priority']);
        $response = $this->actingAs($user)->post('/tickets', $data);
        $ticket = $user->tickets()->sole();
        $response->assertRedirect(route('tickets.show', $ticket));
        $this->assertDatabaseHas('tickets', $data + ['user_id' => $user->id, 'status' => 'new', 'priority' => 'medium']);
    }

    public function test_list_shows_only_own_tickets(): void
    {
        $user = User::factory()->create();
        $own = Ticket::factory()->for($user)->create(['title' => 'Own visible title']);
        $other = Ticket::factory()->create(['title' => 'Other private title']);
        $this->actingAs($user)->get('/tickets')->assertOk()->assertSee($own->title)->assertDontSee($other->title);
    }

    public function test_user_can_view_own_ticket_and_edit_form(): void
    {
        $ticket = Ticket::factory()->create();
        $this->actingAs($ticket->user)->get("/tickets/{$ticket->id}")->assertOk()->assertSee($ticket->title);
        $this->get("/tickets/{$ticket->id}/edit")->assertOk();
        $this->get('/tickets/create')->assertOk();
    }

    public function test_foreign_ticket_is_forbidden_for_all_operations(): void
    {
        $ticket = Ticket::factory()->create();
        $this->actingAs(User::factory()->create());
        $this->get("/tickets/{$ticket->id}")->assertForbidden();
        $this->get("/tickets/{$ticket->id}/edit")->assertForbidden();
        $this->put("/tickets/{$ticket->id}", $this->data())->assertForbidden();
        $this->delete("/tickets/{$ticket->id}")->assertForbidden();
        $this->assertNotSoftDeleted($ticket);
    }

    public function test_owner_can_edit_ticket_without_changing_its_status(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'in_progress']);
        $data = $this->data();
        $this->actingAs($ticket->user)->put("/tickets/{$ticket->id}", $data)->assertRedirect("/tickets/{$ticket->id}");
        $this->assertDatabaseHas('tickets', $data + ['id' => $ticket->id, 'status' => 'in_progress']);
    }

    public function test_ticket_is_soft_deleted_and_no_longer_visible(): void
    {
        $ticket = Ticket::factory()->create();
        $this->actingAs($ticket->user)->delete("/tickets/{$ticket->id}")->assertRedirect('/tickets');
        $this->assertSoftDeleted($ticket);
        $this->assertNotNull(Ticket::withTrashed()->find($ticket->id));
        $this->get("/tickets/{$ticket->id}")->assertNotFound();
        $this->get('/tickets')->assertDontSee($ticket->title);
    }

    public function test_invalid_data_is_rejected(): void
    {
        $this->actingAs(User::factory()->create())->post('/tickets', ['title' => str_repeat('x', 256), 'description' => '', 'category_id' => 999999, 'priority' => 'urgent'])
            ->assertSessionHasErrors(['title', 'description', 'category_id', 'priority']);
        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_owner_cannot_forge_author_or_status_on_create_or_update(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'new']);
        $data = $this->data() + ['status' => 'closed', 'user_id' => 999];
        $this->actingAs($ticket->user)->post('/tickets', $data)->assertSessionHasErrors(['status', 'user_id']);
        $this->put("/tickets/{$ticket->id}", $data)->assertSessionHasErrors(['status', 'user_id']);
        $this->assertSame('new', $ticket->fresh()->status);
    }

    public function test_dashboard_counts_only_own_active_tickets(): void
    {
        $user = User::factory()->create();
        Ticket::factory()->for($user)->count(2)->create(['status' => 'new']);
        Ticket::factory()->for($user)->create(['status' => 'closed'])->delete();
        Ticket::factory()->create(['status' => 'resolved']);
        $this->actingAs($user)->get('/dashboard')->assertOk()->assertViewHas('counts', fn ($counts) => $counts->sum() === 2 && (int) $counts['new'] === 2);
    }

    public function test_missing_ticket_returns_404_and_content_is_escaped(): void
    {
        $ticket = Ticket::factory()->create(['title' => '<script>alert(1)</script>']);
        $this->actingAs($ticket->user)->get('/tickets/999999')->assertNotFound();
        $this->get("/tickets/{$ticket->id}")->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
    }
}
