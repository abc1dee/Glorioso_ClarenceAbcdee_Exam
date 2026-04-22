<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $guest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'full_name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->guest = User::create([
            'full_name' => 'Guest User',
            'email' => 'guest@test.com',
            'password' => bcrypt('password'),
            'role' => 'guest',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_create_a_product()
    {
        $response = $this->actingAs($this->admin)->postJson('/api/products', [
            'name' => 'Laptop',
            'price' => 1200.00,
            'stock' => 50,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('products', [
            'name' => 'Laptop',
            'price' => 1200.00
        ]);
    }

    public function test_guest_cannot_create_a_product()
    {
        $response = $this->actingAs($this->guest)->postJson('/api/products', [
            'name' => 'Laptop',
            'price' => 1200.00,
            'stock' => 50,
        ]);

        $response->assertStatus(403);
    }

    public function test_anyone_can_view_all_products()
    {
        Product::create([
            'name' => 'Phone',
            'price' => 600.00,
            'stock' => 10,
        ]);

        Product::create([
            'name' => 'Tablet',
            'price' => 300.00,
            'stock' => 5,
        ]);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200);
        
        $json = $response->json();
        
        $this->assertCount(2, $json);
        $this->assertEquals('Phone', $json[0]['name']);
        $this->assertEquals('Tablet', $json[1]['name']);
    }

    public function test_admin_can_update_a_product()
    {
        $product = Product::create([
            'name' => 'Old Name',
            'price' => 100.00,
            'stock' => 1,
        ]);

        $response = $this->actingAs($this->admin)->putJson("/api/products/{$product->id}", [
            'name' => 'New Name',
            'price' => 200.00,
            'stock' => 50,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'New Name',
            'price' => 200.00,
        ]);
    }

    public function test_admin_can_delete_a_product()
    {
        $product = Product::create([
            'name' => 'Delete Me',
            'price' => 100.00,
            'stock' => 1,
        ]);

        $response = $this->actingAs($this->admin)->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }
}
