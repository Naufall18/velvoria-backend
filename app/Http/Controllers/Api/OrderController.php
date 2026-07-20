<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\OrderCreationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Services\OrderService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly OrderService $orders)
    {
    }

    // List all orders for the authenticated user
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $orders = Order::with(['items', 'payment'])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['orders' => $orders]);
    }

    // Show a single order (must belong to the authenticated user)
    public function show(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            return $this->respondError('Pesanan tidak ditemukan.', 404);
        }

        $order->load(['items.product.primaryImage', 'payment']);

        return response()->json(['order' => $order]);
    }

    // Create order(s) from the authenticated user's cart. Splitting per vendor,
    // stock locking and payment records live in OrderService; this method only
    // translates HTTP in/out.
    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            $orders = $this->orders->createFromCart(
                $request->user(),
                $request->only([
                    'shipping_name', 'shipping_phone', 'shipping_address',
                    'shipping_city', 'shipping_province', 'shipping_postal_code', 'notes',
                ]),
                $request->input('payment_method') // 'cod' | 'midtrans'
            );
        } catch (OrderCreationException $e) {
            // Same error contract the clients already parse: {"error": "..."} + 422.
            return response()->json(['error' => $e->getMessage()], 422);
        }

        return response()->json(['orders' => $orders], 201);
    }
}
