<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_only_a_regular_user_with_hashed_password(): void
    {
        $this->post('/register', ['name' => 'Demo', 'email' => 'demo@example.test', 'password' => 'Password123!', 'password_confirmation' => 'Password123!', 'role' => 'admin'])
            ->assertRedirect('/dashboard');
        $user = User::where('email', 'demo@example.test')->firstOrFail();
        $this->assertSame('user', $user->role);
        $this->assertTrue(Hash::check('Password123!', $user->password));
        $this->assertAuthenticatedAs($user);
    }

    public function test_registration_validates_email_and_password_confirmation(): void
    {
        User::factory()->create(['email' => 'demo@example.test']);
        $this->post('/register', ['name' => '', 'email' => 'demo@example.test', 'password' => 'short', 'password_confirmation' => 'different'])
            ->assertSessionHasErrors(['name', 'email', 'password']);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_user_can_log_in_and_log_out(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Password123!')]);
        $this->post('/login', ['email' => $user->email, 'password' => 'Password123!'])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_invalid_password_does_not_log_in(): void
    {
        $user = User::factory()->create();
        $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_attempts_are_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'limited@example.test', 'password' => 'wrong'])->assertStatus(302);
        }
        $this->post('/login', ['email' => 'limited@example.test', 'password' => 'wrong'])->assertStatus(429);
    }

    public function test_guests_are_redirected_from_private_pages(): void
    {
        foreach (['/dashboard', '/tickets', '/tickets/create', '/profile', '/admin', '/admin/ticket'] as $path) {
            $this->get($path)->assertRedirect('/login');
        }
    }

    public function test_guest_auth_pages_render(): void
    {
        $this->get('/login')->assertOk()->assertSee('Войти');
        $this->get('/register')->assertOk()->assertSee('Зарегистрироваться');
    }
}
