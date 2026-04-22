<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_register()
    {
        $response = $this->postJson('/api/register', [
            'full_name' => 'Jane Doe',
            'email' => 'jane@bug.com',
            'password' => 'SafePass123',
            'password_confirmation' => 'SafePass123'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'email' => 'jane@bug.com',
            'role' => 'guest'
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'description' => "User jane@bug.com registered."
        ]);
    }

    public function test_email_is_locked_after_five_failed_attempts()
    {
        $user = User::create([
            'full_name' => 'Locker',
            'email' => 'locking@bug.com',
            'password' => bcrypt('correctPassword'),
            'role' => 'guest',
            'login_attempts' => 0,
            'is_active' => true
        ]);

        // Attempt 1 to 5 Incorrect
        for ($i = 1; $i <= 5; $i++) {
            $response = $this->postJson('/api/login', [
                'email' => 'locking@bug.com',
                'password' => 'wrong',
            ]);
            $response->assertStatus(401);
        }

        // Check if locked
        $user->refresh();
        $this->assertNotNull($user->locked_until);
        $this->assertEquals(5, $user->login_attempts);

        // Attempt 6 (Locked Out message)
        $responseLocked = $this->postJson('/api/login', [
            'email' => 'locking@bug.com',
            'password' => 'wrong', // Doesn't matter if right or wrong now
        ]);

        $responseLocked->assertStatus(423)->assertJson([
            'message' => 'Account locked. Try again in 5 minutes.'
        ]);
    }

    public function test_user_can_login_and_retrieve_token()
    {
        $user = User::create([
            'full_name' => 'Login User',
            'email' => 'login@bug.com',
            'password' => bcrypt('password123'),
            'role' => 'guest',
            'is_active' => true
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'login@bug.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['token', 'user']);
    }

    public function test_user_gets_logged_out()
    {
        $user = User::create([
            'full_name' => 'Logout User',
            'email' => 'logout@bug.com',
            'password' => bcrypt('password123'),
            'role' => 'guest',
            'is_active' => true
        ]);

        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
                         ->postJson('/api/logout');

        $response->assertStatus(200);
        $this->assertCount(0, $user->tokens);
    }
}
