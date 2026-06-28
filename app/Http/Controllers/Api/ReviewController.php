<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    // Public: list reviews for a given product.
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        $reviews = Review::with(['user:id,name'])
            ->where('product_id', $validated['product_id'])
            ->latest()
            ->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $reviews,
        ]);
    }

    // Auth: create a review. Marked verified if the user actually bought the product.
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'rating'     => ['required', 'integer', 'min:1', 'max:5'],
            'comment'    => ['nullable', 'string', 'max:2000'],
            'images'     => ['nullable', 'array', 'max:5'],
            'images.*'   => ['string', 'max:500'],
        ]);

        // One review per user per product.
        if (Review::where('user_id', $user->id)->where('product_id', $validated['product_id'])->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You have already reviewed this product.',
            ], 422);
        }

        // Verified purchase: the user has an order containing this product.
        $purchase = OrderItem::where('product_id', $validated['product_id'])
            ->whereHas('order', fn ($q) => $q->where('user_id', $user->id))
            ->first();

        $review = DB::transaction(function () use ($user, $validated, $purchase) {
            $review = Review::create([
                'user_id'     => $user->id,
                'product_id'  => $validated['product_id'],
                'order_id'    => $purchase?->order_id,
                'rating'      => $validated['rating'],
                'comment'     => $validated['comment'] ?? null,
                'images'      => $validated['images'] ?? null,
                'is_verified' => (bool) $purchase,
            ]);

            $this->recalculateProductRating($validated['product_id']);

            return $review;
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Review submitted',
            'data' => ['review' => $review->load('user:id,name')],
        ], 201);
    }

    public function destroy(Request $request, Review $review): JsonResponse
    {
        if ($review->user_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden'], 403);
        }

        $productId = $review->product_id;
        $review->delete();
        $this->recalculateProductRating($productId);

        return response()->json([
            'status' => 'success',
            'message' => 'Review deleted',
        ]);
    }

    // Keep the denormalized rating/total_reviews on the product in sync.
    private function recalculateProductRating(int $productId): void
    {
        $stats = Review::where('product_id', $productId)
            ->selectRaw('COUNT(*) as total, AVG(rating) as average')
            ->first();

        Product::where('id', $productId)->update([
            'total_reviews' => (int) $stats->total,
            'rating'        => round((float) ($stats->average ?? 0), 2),
        ]);
    }
}
