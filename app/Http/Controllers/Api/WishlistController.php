<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = $request->user()->wishlists()
            ->with(['product.primaryImage'])
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => ['items' => $items],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        // Idempotent: don't create duplicates (table has a unique constraint).
        $item = $request->user()->wishlists()->firstOrCreate([
            'product_id' => $validated['product_id'],
        ]);

        return response()->json([
            'status' => 'success',
            'message' => $item->wasRecentlyCreated ? 'Added to wishlist' : 'Already in wishlist',
            'data' => ['item' => $item->load('product.primaryImage')],
        ], $item->wasRecentlyCreated ? 201 : 200);
    }

    public function destroy(Request $request, Wishlist $wishlist): JsonResponse
    {
        if ($wishlist->user_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden'], 403);
        }

        $wishlist->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Removed from wishlist',
        ]);
    }
}
