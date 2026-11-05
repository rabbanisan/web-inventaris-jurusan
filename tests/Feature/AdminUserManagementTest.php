<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_view_user_index()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
             ->get(route('admin.users.index'))
             ->assertOk();
    }

    /** @test */
    public function regular_user_cannot_access_admin_user_routes()
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
             ->get(route('admin.users.index'))
             ->assertStatus(403);
    }

    /** @test */
    public function admin_can_create_a_user()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $payload = [
            'name'                  => 'New User',
            'email'                 => 'newuser@example.com',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
            'role'                  => 'user',
        ];

        $this->actingAs($admin)
             ->post(route('admin.users.store'), $payload)
             ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'role'  => 'user',
        ]);
    }
}
