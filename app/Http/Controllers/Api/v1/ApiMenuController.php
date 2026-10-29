<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiMenuController extends Controller
{
    public function categories(): JsonResponse
    {
        $categories = Category::active()
            ->withCount(['products' => fn($q) => $q->where('is_available', true)])
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'description' => $c->description,
                'image' => $c->image_url,
                'products_count' => $c->products_count,
            ]);

        return response()->json(['success' => true, 'data' => $categories]);
    }

    public function products(Request $request): JsonResponse
    {
        $query = Product::available()->with(['category', 'variations']);

        if ($request->filled('category')) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->category));
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%"));
        }

        if ($request->filled('diet')) {
            if ($request->diet === 'veg') {
                $query->veg();
            } elseif ($request->diet === 'non-veg') {
                $query->nonVeg();
            }
        }

        $sort = $request->query('sort', 'popular');
        match ($sort) {
            'price_low' => $query->orderByRaw('COALESCE(discount_price, regular_price) ASC'),
            'price_high' => $query->orderByRaw('COALESCE(discount_price, regular_price) DESC'),
            'name' => $query->orderBy('name', 'ASC'),
            default => $query->orderBy('is_popular', 'DESC')->orderBy('is_best_seller', 'DESC'),
        };

        $paginated = $query->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $paginated->items(),
            'current_page' => $paginated->currentPage(),
            'total_pages' => $paginated->lastPage(),
            'total_items' => $paginated->total(),
        ]);
    }

    public function productDetails(string $slug): JsonResponse
    {
        $product = Product::available()
            ->where('slug', $slug)
            ->with(['category', 'images', 'variations', 'addonGroups.activeAddons'])
            ->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Food item not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'regular_price' => (float) $product->regular_price,
                'discount_price' => (float) $product->discount_price,
                'current_price' => (float) $product->current_price,
                'is_veg' => $product->is_veg,
                'prep_time' => $product->prep_time_minutes,
                'image' => $product->main_image_url,
                'variations' => $product->activeVariations,
                'addon_groups' => $product->addonGroups,
            ],
        ]);
    }
}
