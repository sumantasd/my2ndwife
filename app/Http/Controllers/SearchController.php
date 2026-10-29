<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $query = $request->query('query') ?? $request->query('q') ?? $request->query('search');
        $products = collect();

        if ($query) {
            $search = trim($query);
            $products = Product::available()
                ->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('category', function ($cq) use ($search) {
                            $cq->where('name', 'like', "%{$search}%");
                        });
                })
                ->with(['category', 'variations', 'addonGroups.activeAddons'])
                ->paginate(12)
                ->withQueryString();
        }

        $popularSearches = ['Awadhi Biryani', 'Smash Burger', 'Sourdough Pizza', 'Butter Chicken', 'Gulab Jamun'];
        $categories = Category::active()->get();

        return view('customer.search', compact('query', 'products', 'popularSearches', 'categories'));
    }
}
