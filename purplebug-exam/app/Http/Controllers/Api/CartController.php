<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $cart = Cart::with('product')->where('user_id', $request->user()->id)->get();
        $total = $cart->sum(fn($item) => $item->quantity * $item->product->price);
        return response()->json(['items' => $cart, 'total' => $total]);
    }

    public function add(Request $request)
    {
        $request->validate(['product_id' => 'required|exists:products,id']);

        $product = Product::findOrFail($request->product_id);

        if ($product->stock < 1) {
            return response()->json(['message' => 'Product is out of stock.'], 422);
        }

        $cartItem = Cart::where('user_id', $request->user()->id)
                        ->where('product_id', $product->id)
                        ->first();

        if ($cartItem) {
            // Prevent adding more than available stock
            if ($cartItem->quantity + 1 > $product->stock) {
                return response()->json(['message' => 'Cannot add more. Stock limit reached.'], 422);
            }
            $cartItem->increment('quantity');
        } else {
            Cart::create([
                'user_id'    => $request->user()->id,
                'product_id' => $product->id,
                'quantity'   => 1,
            ]);
        }

        return response()->json(['message' => 'Product added to cart.']);
    }

    public function remove(Request $request, Cart $cart)
    {
        if ($cart->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }
        $cart->delete();
        return response()->json(['message' => 'Item removed.']);
    }
}
