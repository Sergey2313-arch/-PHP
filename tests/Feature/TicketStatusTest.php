<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_change_only_status_without_resubmitting_ticket_fields(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'new', 'title' => 'Исходная заявка', 'priority' => 'low']);
        $this->actingAs(User::factory()->admin()->create())
            ->patch(route('admin.ticket.status', $ticket), ['status' => 'under_review', 'title' => 'Forged title', 'user_id' => 999, 'priority' => 'critical'])
            ->assertRedirect('/admin/ticket');

        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'status' => 'under_review', 'title' => 'Исходная заявка', 'priority' => 'low', 'user_id' => $ticket->user_id]);
        $this->assertAuthenticated();
    }

    public function test_guest_and_regular_user_cannot_use_admin_status_action(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'new']);
        $this->patch(route('admin.ticket.status', $ticket), ['status' => 'closed'])->assertRedirect('/login');
        $this->actingAs($ticket->user)->patch(route('admin.ticket.status', $ticket), ['status' => 'closed'])->assertForbidden();
        $this->assertSame('new', $ticket->fresh()->status);
    }

    public function test_unknown_or_missing_status_does_not_change_ticket(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'new']);
        $this->actingAs(User::factory()->admin()->create());
        foreach (['invalid', null, ['closed']] as $status) {
            $this->patchJson(route('admin.ticket.status', $ticket), ['status' => $status])->assertUnprocessable()->assertJsonValidationErrors('status');
        }
        $this->assertSame('new', $ticket->fresh()->status);
    }

    public function test_soft_deleted_ticket_cannot_be_updated_by_status_action(): void
    {
        $ticket = Ticket::factory()->create();
        $ticket->delete();
        $this->actingAs(User::factory()->admin()->create())->patch(route('admin.ticket.status', $ticket), ['status' => 'closed'])->assertNotFound();
    }

    public function test_status_filter_applies_to_ajax_rows_and_excludes_deleted_tickets(): void
    {
        $expected = Ticket::factory()->create(['status' => 'under_review', 'title' => 'Visible review ticket']);
        Ticket::factory()->create(['status' => 'in_progress', 'title' => 'Hidden progress ticket']);
        Ticket::factory()->create(['status' => 'under_review', 'title' => 'Deleted review ticket'])->delete();
        $response = $this->actingAs(User::factory()->admin()->create())
            ->postJson('/admin/ticket/search?status=under_review', ['draw' => 1, 'start' => 0, 'length' => 10])
            ->assertOk()->assertJsonPath('recordsTotal', 2)->assertJsonPath('recordsFiltered', 1)->assertJsonCount(1, 'data');
        $rows = json_encode($response->json('data'), JSON_UNESCAPED_UNICODE);
        $this->assertStringContainsString($expected->title, $rows);
        $this->assertStringNotContainsString('Hidden progress ticket', $rows);
        $this->assertStringNotContainsString('Deleted review ticket', $rows);
        $this->assertStringContainsString('name="status"', $response->json('data.0.4'));
        $this->assertStringContainsString('value="under_review" selected', $response->json('data.0.4'));
    }

    public function test_list_and_edit_form_expose_all_statuses_and_unfiltered_counters(): void
    {
        Ticket::factory()->create(['status' => 'new']);
        $ticket = Ticket::factory()->create(['status' => 'under_review']);
        $this->actingAs(User::factory()->admin()->create());
        $response = $this->get('/admin/ticket?status=under_review')->assertOk()
            ->assertViewHas('statusCounts', fn ($counts) => $counts->sum() === 2 && (int) $counts['new'] === 1);
        foreach (Ticket::STATUSES as $label) {
            $response->assertSee($label);
            $this->get("/admin/ticket/{$ticket->id}/edit")->assertOk()->assertSee($label);
        }
    }

    public function test_invalid_filter_is_rejected_instead_of_breaking_table(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->postJson('/admin/ticket/search?status=unknown', ['draw' => 1, 'start' => 0, 'length' => 10])
            ->assertUnprocessable()->assertJsonValidationErrors('status');
    }

    public function test_status_action_returns_to_validated_filter_only(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'new']);
        $this->actingAs(User::factory()->admin()->create())
            ->patch(route('admin.ticket.status', $ticket), ['status' => 'in_progress', 'return_status' => 'new'])
            ->assertRedirect('/admin/ticket?status=new');
        $this->patchJson(route('admin.ticket.status', $ticket), ['status' => 'closed', 'return_status' => 'https://example.org'])
            ->assertUnprocessable()->assertJsonValidationErrors('return_status');
        $this->assertSame('in_progress', $ticket->fresh()->status);
    }

    public function test_owner_sees_new_status_and_dashboard_count(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'under_review']);
        $this->actingAs($ticket->user)->get('/tickets')->assertOk()->assertSee('На рассмотрении');
        $this->get('/dashboard')->assertOk()->assertSee('На рассмотрении')
            ->assertViewHas('counts', fn ($counts) => (int) $counts['under_review'] === 1);
    }

    public function test_status_migration_can_roll_back_without_losing_ticket(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'under_review']);
        $migration = require database_path('migrations/2026_10_04_000001_add_under_review_ticket_status.php');
        $migration->down();
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'status' => 'new']);
        $migration->up();
        $ticket->refresh();
        $ticket->status = 'under_review';
        $ticket->save();
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'status' => 'under_review']);
    }
}
