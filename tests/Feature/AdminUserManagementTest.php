<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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
            // Sengaja 'admin': nilai default kolom adalah 'user', sehingga
            // regresi "role dibuang oleh mass assignment" langsung terlihat.
            'role'                  => 'admin',
        ];

        $this->actingAs($admin)
             ->post(route('admin.users.store'), $payload)
             ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'role'  => 'admin',
        ]);

        $created = User::where('email', 'newuser@example.com')->firstOrFail();
        $this->assertTrue(Hash::check('secret123', $created->password));
    }

    /** @test */
    public function admin_can_update_a_user_role()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($admin)
             ->put(route('admin.users.update', $user->id), [
                 'name'  => 'Updated Name',
                 'email' => $user->email,
                 'role'  => 'admin',
             ])
             ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'id'   => $user->id,
            'name' => 'Updated Name',
            'role' => 'admin',
        ]);
    }

    /** @test */
    public function updating_without_password_keeps_existing_password()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create([
            'role'     => 'user',
            'password' => 'old-secret',
        ]);

        $this->actingAs($admin)
             ->put(route('admin.users.update', $user->id), [
                 'name'                  => $user->name,
                 'email'                 => $user->email,
                 'role'                  => 'user',
                 'password'              => '',
                 'password_confirmation' => '',
             ])
             ->assertRedirect(route('admin.users.index'));

        $this->assertTrue(Hash::check('old-secret', $user->refresh()->password));
    }
}
