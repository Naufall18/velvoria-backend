<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Order;
use App\Services\MidtransPaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class PaymentController extends Controller
{
    protected $midtrans;

    public function __construct(MidtransPaymentService $midtrans)
    {
        $this->midtrans = $midtrans;
    }

    /**
     * Initiate checkout: create Payment record and return Snap token
     */
    public function checkout(Request $request): JsonResponse
    {
        $user = $request->user();

        $order = Order::with(['items', 'user'])->where('id', $request->input('order_id'))
            ->where('user_id', $user->id)
            ->first();

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        try {
            return DB::transaction(function () use ($order) {
                // create payment record (pending)
                $payment = Payment::create([
                    'order_id' => $order->id,
                    'payment_method' => 'midtrans_snap',
                    'payment_gateway' => 'midtrans',
                    'amount' => $order->total,
                    'status' => 'pending',
                ]);

                // Order ID for Midtrans (unique per request)
                $midtransOrderId = $order->order_number . '-' . time();
                
                $params = $this->midtrans->buildTransactionDetails($order, $midtransOrderId);
                $snapToken = $this->midtrans->createSnapToken($params);

                $payment->metadata = [
                    'midtrans_order_id' => $midtransOrderId,
                    'snap_token' => $snapToken,
                ];
                $payment->save();

                return response()->json([
                    'payment' => $payment,
                    'snap_token' => $snapToken,
                ], 201);
            });
        } catch (Exception $e) {
            Log::error('Midtrans checkout error: ' . $e->getMessage());
            return response()->json(['error' => 'Payment initiation failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Webhook / notification endpoint (public) to update payment status
     */
    public function notification(Request $request): JsonResponse
    {
        $payload = $request->all();
        $signature = $request->header('X-Callback-Signature') ?? $payload['signature_key'] ?? '';

        if (!$this->midtrans->verifyWebhookSignature($payload, $signature)) {
            Log::warning('Midtrans webhook invalid signature', ['payload' => $payload]);
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        $midtransOrderId = $payload['order_id'];
        $transactionId = $payload['transaction_id'];
        $status = $payload['transaction_status'];
        
        // Extract real order number (remove timestamp suffix)
        $orderNumber = explode('-', $midtransOrderId)[0] . '-' . explode('-', $midtransOrderId)[1] . '-' . explode('-', $midtransOrderId)[2];

        $order = Order::where('order_number', $orderNumber)->first();
        if (!$order) {
            Log::error('Order not found in webhook', ['order_number' => $orderNumber]);
            return response()->json(['error' => 'Order not found'], 404);
        }

        $payment = Payment::where('order_id', $order->id)->latest()->first();
        if (!$payment) {
            return response()->json(['error' => 'Payment not found'], 404);
        }

        $internalStatus = $this->midtrans->mapStatus($status);

        $payment->transaction_id = $transactionId;
        $payment->status = $internalStatus;
        $payment->metadata = array_merge($payment->metadata ?? [], $payload);
        
        if ($internalStatus === 'completed') {
            $payment->paid_at = now();
            $order->status = 'processing';
            $order->save();
        } elseif ($internalStatus === 'failed') {
            $order->status = 'cancelled';
            $order->save();
        }

        $payment->save();

        return response()->json(['ok' => true]);
    }
}