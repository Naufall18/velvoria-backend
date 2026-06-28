<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
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

    // Create order(s) from the authenticated user's cart.
    // This is a multi-vendor marketplace and the orders table is scoped to one
    // vendor, so a cart spanning multiple vendors is split into one order each.
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $user = $request->user();
        $paymentMethod = $request->input('payment_method'); // 'cod' | 'midtrans'

        // Stock check + decrement + order creation must be atomic.
        return DB::transaction(function () use ($user, $request, $paymentMethod) {
            // Cart is a flat line-item table: one row per product/variant.
            $cartItems = Cart::with(['product', 'variant'])
                ->where('user_id', $user->id)
                ->get();

            if ($cartItems->isEmpty()) {
                return response()->json(['error' => 'Cart is empty!'], 422);
            }

            // Validate every line and group them by vendor.
            $linesByVendor = [];

            foreach ($cartItems as $ci) {
                $product = $ci->product;

                if (!$product) {
                    return response()->json([
                        'error' => 'A product in your cart is no longer available.',
                    ], 422);
                }

                // Lock the product row to avoid overselling under concurrency.
                $locked = Product::where('id', $product->id)->lockForUpdate()->first();

                if ($locked->track_stock && $locked->stock < $ci->quantity) {
                    return response()->json([
                        'error' => "Insufficient stock for \"{$locked->name}\". Available: {$locked->stock}.",
                    ], 422);
                }

                $linesByVendor[$locked->vendor_id][] = [
                    'model'    => $locked,
                    'cart'     => $ci,
                    'subtotal' => $ci->quantity * $locked->price,
                ];
            }

            $createdOrders = [];

            foreach ($linesByVendor as $vendorId => $lines) {
                $subtotal = array_sum(array_column($lines, 'subtotal'));

                // Totals (shipping/tax/voucher to be implemented later).
                $shippingCost = 0;
                $tax = 0;
                $discount = 0;
                $total = $subtotal + $shippingCost + $tax - $discount;

                $order = Order::create([
                    'order_number'         => Order::generateOrderNumber(),
                    'user_id'              => $user->id,
                    'vendor_id'            => $vendorId,
                    'status'               => 'pending',
                    'subtotal'             => $subtotal,
                    'shipping_cost'        => $shippingCost,
                    'tax'                  => $tax,
                    'discount'             => $discount,
                    'total'                => $total,
                    'shipping_name'        => $request->input('shipping_name', $user->name),
                    'shipping_phone'       => $request->input('shipping_phone', $user->phone ?? ''),
                    'shipping_address'     => $request->input('shipping_address', ''),
                    'shipping_city'        => $request->input('shipping_city', ''),
                    'shipping_province'    => $request->input('shipping_province', ''),
                    'shipping_postal_code' => $request->input('shipping_postal_code', ''),
                    'notes'                => $request->input('notes', ''),
                ]);

                foreach ($lines as $line) {
                    $product = $line['model'];
                    $ci = $line['cart'];

                    OrderItem::create([
                        'order_id'           => $order->id,
                        'product_id'         => $ci->product_id,
                        'product_variant_id' => $ci->product_variant_id,
                        'product_name'       => $product->name,
                        'price'              => $product->price,
                        'quantity'           => $ci->quantity,
                        'subtotal'           => $line['subtotal'],
                    ]);

                    // Decrement stock for products that track inventory.
                    if ($product->track_stock) {
                        $product->decrement('stock', $ci->quantity);
                    }
                }

                if ($paymentMethod === 'cod') {
                    // Cash on Delivery: confirm the order now, payment settles on delivery.
                    Payment::create([
                        'order_id'        => $order->id,
                        'payment_method'  => 'cod',
                        'payment_gateway' => null,
                        'amount'          => $total,
                        'status'          => 'pending',
                    ]);

                    $order->status = 'processing';
                    $order->save();
                }
                // For 'midtrans', the order stays 'pending' until the client calls
                // POST /payments/checkout to obtain a Snap token.

                $createdOrders[] = $order->fresh(['items', 'payment']);
            }

            // Clear the user's cart rows.
            Cart::where('user_id', $user->id)->delete();

            return response()->json(['orders' => $createdOrders], 201);
        });
    }
}
