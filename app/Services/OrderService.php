<?php

namespace App\Services;

use App\Exceptions\OrderCreationException;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Create order(s) from the user's cart.
     *
     * This is a multi-vendor marketplace and the orders table is scoped to one
     * vendor, so a cart spanning multiple vendors is split into one order each.
     * Stock check + decrement + order creation run in one transaction.
     *
     * @param  array  $shipping  raw shipping fields + notes from the request
     * @return Collection<int, Order>
     *
     * @throws OrderCreationException
     */
    public function createFromCart(User $user, array $shipping, string $paymentMethod): Collection
    {
        return DB::transaction(function () use ($user, $shipping, $paymentMethod) {
            // Cart is a flat line-item table: one row per product/variant.
            $cartItems = Cart::with(['product', 'variant'])
                ->where('user_id', $user->id)
                ->get();

            if ($cartItems->isEmpty()) {
                throw new OrderCreationException('Cart is empty!');
            }

            $linesByVendor = $this->lockAndGroupByVendor($cartItems);

            $orders = collect();
            foreach ($linesByVendor as $vendorId => $lines) {
                $orders->push(
                    $this->createVendorOrder($user, $vendorId, $lines, $shipping, $paymentMethod)
                );
            }

            // Clear the user's cart rows.
            Cart::where('user_id', $user->id)->delete();

            return $orders;
        });
    }

    // Validate every cart line and group them by vendor. Product rows are
    // locked (SELECT ... FOR UPDATE) to avoid overselling under concurrency.
    private function lockAndGroupByVendor(Collection $cartItems): array
    {
        $linesByVendor = [];

        foreach ($cartItems as $ci) {
            if (!$ci->product) {
                throw new OrderCreationException('A product in your cart is no longer available.');
            }

            $locked = Product::where('id', $ci->product->id)->lockForUpdate()->first();

            if ($locked->track_stock && $locked->stock < $ci->quantity) {
                throw new OrderCreationException(
                    "Insufficient stock for \"{$locked->name}\". Available: {$locked->stock}."
                );
            }

            $linesByVendor[$locked->vendor_id][] = [
                'model'    => $locked,
                'cart'     => $ci,
                'subtotal' => $ci->quantity * $locked->price,
            ];
        }

        return $linesByVendor;
    }

    private function createVendorOrder(
        User $user,
        int $vendorId,
        array $lines,
        array $shipping,
        string $paymentMethod
    ): Order {
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
            'shipping_name'        => $shipping['shipping_name'] ?? $user->name,
            'shipping_phone'       => $shipping['shipping_phone'] ?? ($user->phone ?? ''),
            'shipping_address'     => $shipping['shipping_address'] ?? '',
            'shipping_city'        => $shipping['shipping_city'] ?? '',
            'shipping_province'    => $shipping['shipping_province'] ?? '',
            'shipping_postal_code' => $shipping['shipping_postal_code'] ?? '',
            'notes'                => $shipping['notes'] ?? '',
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

        return $order->fresh(['items', 'payment']);
    }
}
