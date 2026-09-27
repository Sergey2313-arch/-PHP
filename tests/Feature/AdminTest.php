<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_session_remains_valid_when_entering_backpack(): void
    {
        $admin = User::factory()->admin()->create(['password' => Hash::make('Admin12345!')]);
        $this->post('/login', ['email' => $admin->email, 'password' => 'Admin12345!'])->assertRedirect('/dashboard');
        $this->get('/dashboard')->assertOk();
        $this->get('/admin/ticket')->assertOk();
        $this->assertAuthenticatedAs($admin);
    }

    public function test_regular_user_cannot_access_admin_pages_or_mutations(): void
    {
        $ticket = Ticket::factory()->create();
        $this->actingAs($ticket->user);
        foreach (['/admin', '/admin/dashboard', '/admin/user', '/admin/ticket', '/admin/category', '/admin/category/create', "/admin/ticket/{$ticket->id}/edit"] as $url) {
            $this->get($url)->assertForbidden();
        }
        $this->post('/admin/category', ['name' => 'Denied', 'slug' => 'denied'])->assertForbidden();
        $this->put("/admin/ticket/{$ticket->id}", ['status' => 'closed'])->assertForbidden();
        $this->delete("/admin/ticket/{$ticket->id}")->assertForbidden();
        $this->delete("/admin/category/{$ticket->category_id}")->assertForbidden();
    }

    public function test_admin_can_open_lists_details_and_edit_forms(): void
    {
        $admin = User::factory()->admin()->create();
        $ticket = Ticket::factory()->create();
        $this->actingAs($admin)->get('/admin')->assertRedirect('/admin/ticket');
        foreach (['/admin/user', '/admin/ticket', '/admin/category', '/admin/category/create', "/admin/user/{$ticket->user_id}/show", "/admin/ticket/{$ticket->id}/show", "/admin/ticket/{$ticket->id}/edit", "/admin/category/{$ticket->category_id}/show", "/admin/category/{$ticket->category_id}/edit"] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_admin_can_see_all_tickets_in_table_data(): void
    {
        $tickets = Ticket::factory()->count(2)->create();
        $response = $this->actingAs(User::factory()->admin()->create())->postJson('/admin/ticket/search', ['draw' => 1, 'start' => 0, 'length' => 10])->assertOk()->assertJsonPath('recordsTotal', 2);
        $this->assertStringContainsString($tickets->first()->user->email, json_encode($response->json('data')));
    }

    public function test_admin_can_update_status_priority_and_soft_delete_ticket(): void
    {
        $ticket = Ticket::factory()->create();
        $this->actingAs(User::factory()->admin()->create());
        $this->put("/admin/ticket/{$ticket->id}", ['id' => $ticket->id, 'title' => 'Updated by admin', 'description' => 'Updated description', 'category_id' => $ticket->category_id, 'status' => 'resolved', 'priority' => 'critical', 'user_id' => 999])->assertRedirect('/admin/ticket');
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'status' => 'resolved', 'priority' => 'critical', 'user_id' => $ticket->user_id]);
        $this->delete("/admin/ticket/{$ticket->id}")->assertSuccessful();
        $this->assertSoftDeleted($ticket);
    }

    public function test_admin_validation_rejects_invalid_status(): void
    {
        $ticket = Ticket::factory()->create();
        $this->actingAs(User::factory()->admin()->create())->put("/admin/ticket/{$ticket->id}", ['title' => $ticket->title, 'description' => $ticket->description, 'category_id' => $ticket->category_id, 'priority' => 'high', 'status' => 'invalid'])->assertSessionHasErrors('status');
    }

    public function test_admin_can_create_update_and_delete_unused_category(): void
    {
        $this->actingAs(User::factory()->admin()->create())->post('/admin/category', ['name' => 'Demo category', 'slug' => 'demo-category'])->assertSessionHasNoErrors()->assertRedirect();
        $category = Category::where('slug', 'demo-category')->firstOrFail();
        $this->put("/admin/category/{$category->id}", ['id' => $category->id, 'name' => 'Updated category', 'slug' => 'demo-category'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Updated category']);
        $this->delete("/admin/category/{$category->id}")->assertOk();
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_category_used_by_soft_deleted_ticket_cannot_be_deleted(): void
    {
        $ticket = Ticket::factory()->create();
        $ticket->delete();
        $this->actingAs(User::factory()->admin()->create())->deleteJson("/admin/category/{$ticket->category_id}")->assertUnprocessable();
        $this->assertDatabaseHas('categories', ['id' => $ticket->category_id]);
    }

    public function test_admin_users_are_read_only_and_passwords_are_not_displayed(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get("/admin/user/{$admin->id}/show")->assertOk()->assertDontSee($admin->password);
        $this->put("/admin/user/{$admin->id}", ['role' => 'user'])->assertNotFound();
        $this->post('/admin/register', ['name' => 'Intruder'])->assertNotFound();
    }
}
