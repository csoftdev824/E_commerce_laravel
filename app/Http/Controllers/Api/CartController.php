<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    /**
     * Get the authenticated user's cart items.
     */
    public function index(Request $request): JsonResponse
    {
        $cartItems = $request->user()
            ->cartItems()
            ->with('product')
            ->get();

        return response()->json($cartItems);
    }

    /**
     * Add a product to the cart.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        // Check stock availability
        if ($product->stock_quantity < $validated['quantity']) {
            throw ValidationException::withMessages([
                'quantity' => 'Insufficient stock available.',
            ]);
        }

        $cartItem = CartItem::where('user_id', $request->user()->id)
            ->where('product_id', $validated['product_id'])
            ->first();

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $validated['quantity'];
            // Check stock availability with new quantity
            if ($product->stock_quantity < $newQuantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'Insufficient stock available.',
                ]);
            }
            $cartItem->quantity = $newQuantity;
            $cartItem->save();
        } else {
            $cartItem = CartItem::create([
                'user_id' => $request->user()->id,
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'],
            ]);
        }

        $cartItem->load('product');

        return response()->json($cartItem, 201);
    }

    /**
     * Update the quantity of a cart item.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem = CartItem::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $product = $cartItem->product;

        // Check stock availability
        if ($product->stock_quantity < $validated['quantity']) {
            throw ValidationException::withMessages([
                'quantity' => 'Insufficient stock available.',
            ]);
        }

        $cartItem->update(['quantity' => $validated['quantity']]);
        $cartItem->load('product');

        return response()->json($cartItem);
    }

    /**
     * Remove an item from the cart.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $cartItem = CartItem::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $cartItem->delete();

        return response()->json(['message' => 'Item removed from cart'], 200);
    }
}
