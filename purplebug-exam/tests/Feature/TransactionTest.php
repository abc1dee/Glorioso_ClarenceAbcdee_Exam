<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $guest;
    protected $product;

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

        $this->product = Product::create([
            'name' => 'Test Product',
            'price' => 150.00,
            'stock' => 10,
        ]);
    }

    public function test_guest_can_add_product_to_cart()
    {
        $response = $this->actingAs($this->guest)->postJson('/api/cart', [
            'product_id' => $this->product->id
        ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('carts', [
            'user_id' => $this->guest->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);
    }

    public function test_cart_quantity_increments_when_adding_same_product()
    {
        Cart::create([
            'user_id' => $this->guest->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($this->guest)->postJson('/api/cart', [
            'product_id' => $this->product->id
        ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('carts', [
            'user_id' => $this->guest->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);
    }

    public function test_guest_can_checkout_cart_items_and_create_order()
    {
        // Add 2 items to Cart
        Cart::create([
            'user_id' => $this->guest->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($this->guest)->postJson('/api/checkout');

        $response->assertStatus(200);

        // Assert order was created
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->guest->id,
            'total' => 300.00, // 2 * 150 = 300
            'status' => 'pending'
        ]);

        // Assert order items were mapped
        $this->assertDatabaseHas('order_items', [
            'product_id' => $this->product->id,
            'quantity' => 2,
            'price' => 150.00
        ]);

        // Assert cart was emptied
        $this->assertDatabaseMissing('carts', [
            'user_id' => $this->guest->id,
        ]);

        // Assert product stock was deducted
        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'stock' => 8 // 10 original - 2 checked out
        ]);
    }

    public function test_admin_can_update_order_status()
    {
        $order = Order::create([
            'user_id' => $this->guest->id,
            'total' => 150.00,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->admin)->patchJson("/api/orders/{$order->id}/status", [
            'status' => 'for_delivery'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'for_delivery'
        ]);
    }
}
