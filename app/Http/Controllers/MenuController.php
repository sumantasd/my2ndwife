<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::active()->withCount(['products' => function ($q) {
            $q->where('is_available', true);
        }])->get();

        $query = Product::available()
            ->with(['category', 'variations', 'addonGroups.activeAddons']);

        // Filter by Category
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->query('category'));
            });
        }

        // Search by Food Name or Description
        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by Dietary Preference (veg / non-veg)
        if ($request->filled('diet')) {
            if ($request->query('diet') === 'veg') {
                $query->veg();
            } elseif ($request->query('diet') === 'non-veg') {
                $query->nonVeg();
            }
        }

        // Sorting
        $sort = $request->query('sort', 'popular');
        match ($sort) {
            'price_low' => $query->orderByRaw('COALESCE(discount_price, regular_price) ASC'),
            'price_high' => $query->orderByRaw('COALESCE(discount_price, regular_price) DESC'),
            'name' => $query->orderBy('name', 'ASC'),
            default => $query->orderBy('is_popular', 'DESC')->orderBy('is_best_seller', 'DESC')->orderBy('display_order', 'ASC'),
        };

        $products = $query->paginate(24)->withQueryString();
        $selectedCategory = $request->query('category');
        $viewMode = $request->query('view', 'grid');

        return view('customer.menu', compact('categories', 'products', 'selectedCategory', 'viewMode'));
    }

    public function show(Request $request, string $slug): View|JsonResponse
    {
        $product = Product::available()
            ->where('slug', $slug)
            ->with(['category', 'images', 'variations', 'addonGroups.activeAddons'])
            ->firstOrFail();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'description' => $product->description,
                    'regular_price' => (float) $product->regular_price,
                    'discount_price' => (float) $product->discount_price,
                    'current_price' => (float) $product->current_price,
                    'has_discount' => $product->has_discount,
                    'discount_percent' => $product->discount_percent,
                    'is_veg' => $product->is_veg,
                    'prep_time' => $product->prep_time_minutes,
                    'image' => $product->main_image_url,
                    'variations' => $product->activeVariations->map(fn($v) => [
                        'id' => $v->id,
                        'name' => $v->name,
                        'price' => (float) $v->price,
                        'is_default' => $v->is_default,
                    ]),
                    'addon_groups' => $product->addonGroups->map(fn($g) => [
                        'id' => $g->id,
                        'name' => $g->name,
                        'min_selection' => $g->min_selection,
                        'max_selection' => $g->max_selection,
                        'is_required' => $g->is_required,
                        'addons' => $g->activeAddons->map(fn($a) => [
                            'id' => $a->id,
                            'name' => $a->name,
                            'price' => (float) $a->price,
                        ]),
                    ]),
                ]
            ]);
        }

        $relatedProducts = Product::available()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['category', 'variations'])
            ->take(4)
            ->get();

        return view('customer.product-details', compact('product', 'relatedProducts'));
    }
}
