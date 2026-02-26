<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        $products = Product::query()
            ->latest()
            ->get([
                'id',
                'title',
                'brand',
                'price',
                'currency',
                'category',
                'rating',
                'review_count',
                'availability',
                'images',
            ]);

        return response()->json([
            'data' => $products,
        ]);
    }
}
