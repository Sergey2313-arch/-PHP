<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_and_update_profile_but_cannot_change_role(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/profile')->assertOk();
        $this->patch('/profile', ['name' => 'Updated', 'email' => 'updated@example.test', 'role' => 'admin'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated', 'email' => 'updated@example.test', 'role' => 'user']);
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_profile_requires_unique_email(): void
    {
        $other = User::factory()->create();
        $this->actingAs(User::factory()->create())->patch('/profile', ['name' => 'Demo', 'email' => $other->email])->assertSessionHasErrors('email');
    }

    public function test_replacing_avatar_removes_previous_file(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $data = ['name' => $user->name, 'email' => $user->email];
        $this->actingAs($user)->patch('/profile', $data + ['avatar' => UploadedFile::fake()->image('first.png')])->assertSessionHasNoErrors();
        $oldPath = $user->fresh()->avatar;
        Storage::disk('public')->assertExists($oldPath);
        $this->patch('/profile', $data + ['avatar' => UploadedFile::fake()->image('second.png')])->assertSessionHasNoErrors();
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($user->fresh()->avatar);
    }

    public function test_invalid_avatar_is_rejected(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $this->actingAs($user)->patch('/profile', ['name' => $user->name, 'email' => $user->email, 'avatar' => UploadedFile::fake()->create('bad.php', 1, 'text/plain')])->assertSessionHasErrors('avatar');
        $this->assertNull($user->fresh()->avatar);
    }

    public function test_password_change_requires_current_password_and_confirmation(): void
    {
        $user = User::factory()->create(['password' => Hash::make('OldPassword!')]);
        $this->actingAs($user)->put('/profile/password', ['current_password' => 'wrong', 'password' => 'NewPassword!', 'password_confirmation' => 'NewPassword!'])->assertSessionHasErrors('current_password');
        $this->put('/profile/password', ['current_password' => 'OldPassword!', 'password' => 'NewPassword!', 'password_confirmation' => 'NewPassword!'])->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('NewPassword!', $user->fresh()->password));
    }
}
