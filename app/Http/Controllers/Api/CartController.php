<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $carts = $request->user()->carts()
            ->with(['product.primaryImage', 'variant'])
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => ['items' => $carts],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $existing = $request->user()->carts()
            ->where('product_id', $validated['product_id'])
            ->where('product_variant_id', $validated['product_variant_id'] ?? null)
            ->first();

        if ($existing) {
            $existing->increment('quantity', $validated['quantity']);
            $cart = $existing->fresh(['product.primaryImage', 'variant']);
        } else {
            $cart = $request->user()->carts()->create($validated);
            $cart->load(['product.primaryImage', 'variant']);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Item added to cart',
            'data' => ['item' => $cart],
        ], 201);
    }

    public function update(Request $request, Cart $cart): JsonResponse
    {
        if ($cart->user_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Cart updated',
            'data' => ['item' => $cart->load(['product.primaryImage', 'variant'])],
        ]);
    }

    public function destroy(Request $request, Cart $cart): JsonResponse
    {
        if ($cart->user_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden'], 403);
        }

        $cart->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Item removed from cart',
        ]);
    }

    public function clear(Request $request): JsonResponse
    {
        $request->user()->carts()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Cart cleared',
        ]);
    }
}
