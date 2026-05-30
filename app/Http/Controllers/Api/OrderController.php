<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\Cart as CartModel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    // List all orders for the authenticated user
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $orders = Order::with(['items', 'payment'])->where('user_id', $user->id)->orderByDesc('created_at')->get();
        return response()->json(['orders' => $orders]);
    }

    // Create an order from authenticated user's cart
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        // Using DB transaction for safety
        return DB::transaction(function () use ($user, $request) {
            $cart = CartModel::with('items.product')->where('user_id', $user->id)->first();
            if (!$cart || $cart->items->isEmpty()) {
                return response()->json(['error' => 'Cart is empty!'], 422);
            }

            // Calculate totals (stub/simple, must be improved for shipping/voucher/tax later)
            $subtotal = $cart->items->sum(fn($i) => $i->quantity * $i->product->price);
            $shipping_cost = 0;
            $tax = 0;
            $discount = 0;
            $total = $subtotal + $shipping_cost + $tax - $discount;

            $order = Order::create([
                'order_number'       => Order::generateOrderNumber(),
                'user_id'            => $user->id,
                'status'             => 'pending',
                'subtotal'           => $subtotal,
                'shipping_cost'      => $shipping_cost,
                'tax'                => $tax,
                'discount'           => $discount,
                'total'              => $total,
                'shipping_name'      => $request->input('shipping_name', $user->name),
                'shipping_phone'     => $request->input('shipping_phone', $user->phone ?? ''),
                'shipping_address'   => $request->input('shipping_address', ''),
                'shipping_city'      => $request->input('shipping_city', ''),
                'shipping_province'  => $request->input('shipping_province', ''),
                'shipping_postal_code' => $request->input('shipping_postal_code', ''),
                'notes'              => $request->input('notes', ''),
            ]);

            foreach ($cart->items as $ci) {
                OrderItem::create([
                    'order_id'           => $order->id,
                    'product_id'         => $ci->product_id,
                    'product_variant_id' => $ci->product_variant_id,
                    'product_name'       => $ci->product->name,
                    'price'              => $ci->product->price,
                    'quantity'           => $ci->quantity,
                    'subtotal'           => $ci->quantity * $ci->product->price,
                ]);
            }

            // Clear user cart
            $cart->items()->delete();

            return response()->json(['order' => $order->fresh(['items'])], 201);
        });
    }
}