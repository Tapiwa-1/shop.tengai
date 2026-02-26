<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

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
            ])
            ->map(function (Product $product): array {
                return [
                    'id' => $product->id,
                    'title' => $product->title,
                    'brand' => $product->brand,
                    'price' => $product->price,
                    'currency' => $product->currency,
                    'category' => $product->category,
                    'rating' => $product->rating,
                    'review_count' => $product->review_count,
                    'availability' => $product->availability,
                    'images' => $product->images,
                    'image_url' => $this->resolveImageUrl($product),
                ];
            })
            ->values();

        return response()->json([
            'data' => $products,
        ]);
    }

    public function image(string $path): Response
    {
        abort_unless(Storage::disk('public')->exists($path), 404);

        return response(Storage::disk('public')->get($path), 200)
            ->header('Content-Type', Storage::disk('public')->mimeType($path) ?: 'application/octet-stream')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    private function resolveImageUrl(Product $product): ?string
    {
        $image = is_array($product->images) ? ($product->images[0] ?? null) : null;

        if (! is_string($image) || blank($image)) {
            return null;
        }

        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            return $image;
        }

        $path = ltrim(str_replace('storage/', '', $image), '/');

        return route('products.image', ['path' => $path]);
    }
}
