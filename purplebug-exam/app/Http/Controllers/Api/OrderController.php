<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // Guest: see own orders
    public function myOrders(Request $request)
    {
        $orders = Order::with('items.product')->where('user_id', $request->user()->id)->get();
        return response()->json($orders);
    }

    // Admin: see all orders
    public function allOrders()
    {
        return response()->json(Order::with('items.product', 'user')->get());
    }

    public function checkout(Request $request)
    {
        $cartItems = Cart::with('product')->where('user_id', $request->user()->id)->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Cart is empty.'], 422);
        }

        // Validate sufficient stock for every cart item before processing
        foreach ($cartItems as $item) {
            if ($item->quantity > $item->product->stock) {
                return response()->json([
                    'message' => "Insufficient stock for {$item->product->name}. Available: {$item->product->stock}, Requested: {$item->quantity}."
                ], 422);
            }
        }

        $total = $cartItems->sum(fn($item) => $item->quantity * $item->product->price);

        $order = Order::create([
            'user_id' => $request->user()->id,
            'total'   => $total,
            'status'  => 'pending',
        ]);

        foreach ($cartItems as $item) {
            OrderItem::create([
                'order_id'   => $order->id,
                'product_id' => $item->product_id,
                'quantity'   => $item->quantity,
                'price'      => $item->product->price,
            ]);
            // Deduct stock
            $item->product->decrement('stock', $item->quantity);
        }

        // Clear cart
        Cart::where('user_id', $request->user()->id)->delete();

        ActivityLogService::log('checkout', "Order #{$order->id} placed.");
        return response()->json(['message' => 'Order placed successfully.', 'order' => $order]);
    }

    // Admin: update order status
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,for_delivery,delivered,canceled',
        ]);

        $order->update(['status' => $request->status]);
        ActivityLogService::log('update_order_status', "Order #{$order->id} status set to {$request->status}");
        return response()->json($order);
    }

    // Admin: delete an order
    public function destroy(Order $order)
    {
        ActivityLogService::log('delete_order', "Deleted order #{$order->id}");
        $order->items()->delete();
        $order->delete();
        return response()->json(['message' => 'Order deleted.']);
    }
}
