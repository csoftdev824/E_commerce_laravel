<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * Create an order from cart items (checkout).
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Get all cart items with products
        $cartItems = $user->cartItems()->with('product')->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'message' => 'Your cart is empty.',
            ], 422);
        }

        // Validate stock availability and calculate total
        $totalAmount = 0;
        $orderItemsData = [];

        foreach ($cartItems as $cartItem) {
            $product = $cartItem->product;
            
            // Check if product still exists
            if (!$product) {
                return response()->json([
                    'message' => "Product '{$cartItem->product_id}' no longer exists.",
                ], 422);
            }

            // Check stock availability
            if ($product->stock_quantity < $cartItem->quantity) {
                throw ValidationException::withMessages([
                    'cart' => "Insufficient stock for '{$product->name}'. Available: {$product->stock_quantity}, Requested: {$cartItem->quantity}",
                ]);
            }

            $itemTotal = $product->price * $cartItem->quantity;
            $totalAmount += $itemTotal;

            $orderItemsData[] = [
                'product_id' => $product->id,
                'quantity' => $cartItem->quantity,
                'price' => $product->price,
                'product' => $product, // For stock reduction
            ];
        }

        // Create order and order items in a transaction
        DB::beginTransaction();

        try {
            // Create the order
            $order = Order::create([
                'user_id' => $user->id,
                'total_amount' => $totalAmount,
                'status' => 'completed',
            ]);

            // Create order items and reduce stock
            foreach ($orderItemsData as $itemData) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'price' => $itemData['price'],
                ]);

                // Reduce product stock
                // Refresh product from database to ensure original values are tracked correctly
                // The ProductObserver will automatically check and dispatch low stock notification
                // if stock crosses the threshold (was > LOW_STOCK_THRESHOLD, now <= LOW_STOCK_THRESHOLD)
                $product = Product::findOrFail($itemData['product_id']);
                $product->stock_quantity -= $itemData['quantity'];
                $product->save();
            }

            // Clear the cart
            $user->cartItems()->delete();

            DB::commit();

            // Load order with items and products
            $order->load(['orderItems.product']);

            return response()->json([
                'message' => 'Order placed successfully!',
                'order' => $order,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Failed to create order. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's orders.
     */
    public function index(Request $request): JsonResponse
    {
        $orders = $request->user()
            ->orders()
            ->with(['orderItems.product'])
            ->latest()
            ->get();

        return response()->json($orders);
    }

    /**
     * Get a specific order.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $order = $request->user()
            ->orders()
            ->with(['orderItems.product'])
            ->findOrFail($id);

        return response()->json($order);
    }
}
