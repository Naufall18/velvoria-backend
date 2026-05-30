<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;
use Exception;

class MidtransPaymentService
{
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$clientKey = config('services.midtrans.client_key');
        Config::$isProduction = config('services.midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function createSnapToken(array $params): string
    {
        try {
            return Snap::getSnapToken($params);
        } catch (Exception $e) {
            throw new Exception('Failed to create Snap token: ' . $e->getMessage());
        }
    }

    public function getTransactionStatus(string $transactionId): array
    {
        try {
            $status = Transaction::status($transactionId);
            return (array) $status;
        } catch (Exception $e) {
            throw new Exception('Failed to get transaction status: ' . $e->getMessage());
        }
    }

    public function verifyWebhookSignature(array $payload, string $signature): bool
    {
        $orderId = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;

        if (!$orderId || !$statusCode || !$grossAmount) {
            return false;
        }

        $serverKey = config('services.midtrans.server_key');
        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        return hash_equals($expectedSignature, $signature);
    }

    public function mapStatus(string $midtransStatus): string
    {
        $statusMap = [
            'capture' => 'completed',
            'settlement' => 'completed',
            'pending' => 'pending',
            'deny' => 'failed',
            'cancel' => 'failed',
            'expire' => 'failed',
            'failure' => 'failed',
        ];

        return $statusMap[strtolower($midtransStatus)] ?? 'pending';
    }

    public function buildTransactionDetails(object $order, string $orderId): array
    {
        return [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $order->total,
            ],
            'customer_details' => [
                'first_name' => $order->shipping_name,
                'email' => $order->user->email ?? '',
                'phone' => $order->shipping_phone,
                'billing_address' => [
                    'address' => $order->shipping_address,
                    'city' => $order->shipping_city,
                    'postal_code' => $order->shipping_postal_code,
                ],
                'shipping_address' => [
                    'address' => $order->shipping_address,
                    'city' => $order->shipping_city,
                    'postal_code' => $order->shipping_postal_code,
                ],
            ],
            'item_details' => $this->buildItemDetails($order),
        ];
    }

    private function buildItemDetails(object $order): array
    {
        $items = [];

        foreach ($order->items as $item) {
            $items[] = [
                'id' => (string) $item->product_id,
                'price' => (int) $item->price,
                'quantity' => (int) $item->quantity,
                'name' => $item->product_name,
            ];
        }

        return $items;
    }
}