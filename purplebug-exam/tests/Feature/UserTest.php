<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $guest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'full_name' => 'Admin Boss',
            'email' => 'adminboss@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->guest = User::create([
            'full_name' => 'Regular Guest',
            'email' => 'guestuser@test.com',
            'password' => bcrypt('password'),
            'role' => 'guest',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_view_all_users()
    {
        $response = $this->actingAs($this->admin)->getJson('/api/users');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json());
    }

    public function test_guest_cannot_view_all_users()
    {
        $response = $this->actingAs($this->guest)->getJson('/api/users');

        // Based on typical routing, guests shouldn't have access to this admin route
        $response->assertStatus(403);
    }

    public function test_admin_can_create_a_user()
    {
        $response = $this->actingAs($this->admin)->postJson('/api/users', [
            'full_name' => 'New Guy',
            'email' => 'newguy@test.com',
            'password' => 'SafePass123',
            'role' => 'guest'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'email' => 'newguy@test.com',
            'role' => 'guest'
        ]);
    }

    public function test_admin_can_update_a_user()
    {
        $userToUpdate = User::create([
            'full_name' => 'Old Name',
            'email' => 'oldname@test.com',
            'password' => bcrypt('password'),
            'role' => 'guest',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->putJson("/api/users/{$userToUpdate->id}", [
            'full_name' => 'Brand New Name',
            'role' => 'admin'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $userToUpdate->id,
            'full_name' => 'Brand New Name',
            'role' => 'admin'
        ]);
    }

    public function test_admin_can_deactivate_a_user()
    {
        $userToDeactivate = User::create([
            'full_name' => 'Active User',
            'email' => 'active@test.com',
            'password' => bcrypt('password'),
            'role' => 'guest',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->patchJson("/api/users/{$userToDeactivate->id}/deactivate");

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $userToDeactivate->id,
            'is_active' => false
        ]);
    }

    public function test_admin_can_delete_a_user()
    {
        $userToDelete = User::create([
            'full_name' => 'Delete Me',
            'email' => 'delete@test.com',
            'password' => bcrypt('password'),
            'role' => 'guest',
        ]);

        $response = $this->actingAs($this->admin)->deleteJson("/api/users/{$userToDelete->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('users', [
            'id' => $userToDelete->id,
        ]);
    }
}
