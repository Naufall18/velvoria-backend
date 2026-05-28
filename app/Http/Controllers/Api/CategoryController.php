<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::whereNull('parent_id')
            ->with('children')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => ['categories' => $categories],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $category = Category::where('slug', $slug)
            ->with(['children', 'products' => function ($q) {
                $q->where('status', 'active')->limit(20);
            }])
            ->firstOrFail();

        return response()->json([
            'status' => 'success',
            'data' => ['category' => $category],
        ]);
    }
}
