<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AddonGroup;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminProductController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::orderBy('name')->get();

        $query = Product::with(['category', 'variations']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'available') {
                $query->where('is_available', true);
            } elseif ($request->status === 'unavailable') {
                $query->where('is_available', false);
            }
        }

        if ($request->filled('diet')) {
            if ($request->diet === 'veg') {
                $query->where('is_veg', true);
            } elseif ($request->diet === 'non-veg') {
                $query->where('is_veg', false);
            }
        }

        $products = $query->latest()->paginate(15)->withQueryString();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::active()->get();
        $addonGroups = AddonGroup::where('is_active', true)->get();

        return view('admin.products.create', compact('categories', 'addonGroups'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:160', 'unique:products,slug'],
            'description' => ['nullable', 'string'],
            'sku' => ['nullable', 'string', 'max:50', 'unique:products,sku'],
            'regular_price' => ['required', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0', 'lt:regular_price'],
            'is_veg' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'is_popular' => ['nullable', 'boolean'],
            'is_best_seller' => ['nullable', 'boolean'],
            'prep_time_minutes' => ['required', 'integer', 'min:1'],
            'is_available' => ['nullable', 'boolean'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'main_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:3072'],
            'addon_groups' => ['nullable', 'array'],
            'addon_groups.*' => ['exists:addon_groups,id'],
            'variations' => ['nullable', 'array'],
            'variations.*.name' => ['required_with:variations', 'string', 'max:100'],
            'variations.*.price' => ['required_with:variations', 'numeric', 'min:0'],
        ]);

        $imagePath = null;
        if ($request->hasFile('main_image')) {
            $imagePath = $request->file('main_image')->store('food', 'public');
        }

        $slug = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);

        $product = Product::create([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'sku' => !empty($validated['sku']) ? strtoupper($validated['sku']) : 'M2W-' . strtoupper(Str::random(6)),
            'regular_price' => $validated['regular_price'],
            'discount_price' => $validated['discount_price'] ?? null,
            'is_veg' => $request->boolean('is_veg'),
            'is_featured' => $request->boolean('is_featured'),
            'is_popular' => $request->boolean('is_popular'),
            'is_best_seller' => $request->boolean('is_best_seller'),
            'prep_time_minutes' => $validated['prep_time_minutes'],
            'is_available' => $request->boolean('is_available', true),
            'display_order' => $validated['display_order'] ?? 0,
            'main_image' => $imagePath,
        ]);

        if (!empty($validated['addon_groups'])) {
            $product->addonGroups()->sync($validated['addon_groups']);
        }

        if (!empty($validated['variations'])) {
            foreach ($validated['variations'] as $idx => $v) {
                if (!empty($v['name']) && isset($v['price'])) {
                    ProductVariation::create([
                        'product_id' => $product->id,
                        'name' => $v['name'],
                        'price' => $v['price'],
                        'is_default' => $idx === 0,
                        'is_active' => true,
                        'display_order' => $idx + 1,
                    ]);
                }
            }
        }

        AuditLog::record('product_created', 'Product', $product->id, ['name' => $product->name]);

        return redirect()->route('admin.products.index')->with('success', "Food dish '{$product->name}' created successfully.");
    }

    public function edit(Product $product): View
    {
        $categories = Category::active()->get();
        $addonGroups = AddonGroup::where('is_active', true)->get();
        $product->load(['variations', 'addonGroups']);

        return view('admin.products.edit', compact('product', 'categories', 'addonGroups'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['sometimes', 'required', 'exists:categories,id'],
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:160', 'unique:products,slug,' . $product->id],
            'description' => ['nullable', 'string'],
            'sku' => ['nullable', 'string', 'max:50', 'unique:products,sku,' . $product->id],
            'regular_price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0'],
            'is_veg' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'is_popular' => ['nullable', 'boolean'],
            'is_best_seller' => ['nullable', 'boolean'],
            'prep_time_minutes' => ['sometimes', 'required', 'integer', 'min:1'],
            'is_available' => ['nullable', 'boolean'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'main_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:3072'],
            'addon_groups' => ['nullable', 'array'],
            'addon_groups.*' => ['exists:addon_groups,id'],
            'existing_variations' => ['nullable', 'array'],
            'existing_variations.*.name' => ['required', 'string'],
            'existing_variations.*.price' => ['required', 'numeric', 'min:0'],
            'new_variations' => ['nullable', 'array'],
            'new_variations.*.name' => ['nullable', 'string'],
            'new_variations.*.price' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($request->hasFile('main_image')) {
            $validated['main_image'] = $request->file('main_image')->store('food', 'public');
        } else {
            unset($validated['main_image']);
        }

        if (!empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['slug']);
        }

        if ($request->exists('is_veg')) {
            $validated['is_veg'] = $request->boolean('is_veg');
        } else {
            unset($validated['is_veg']);
        }

        if ($request->exists('is_featured')) {
            $validated['is_featured'] = $request->boolean('is_featured');
        } else {
            unset($validated['is_featured']);
        }

        if ($request->exists('is_popular')) {
            $validated['is_popular'] = $request->boolean('is_popular');
        } else {
            unset($validated['is_popular']);
        }

        if ($request->exists('is_best_seller')) {
            $validated['is_best_seller'] = $request->boolean('is_best_seller');
        } else {
            unset($validated['is_best_seller']);
        }

        // Availability: Only update if explicitly submitted; otherwise preserve current availability
        if ($request->exists('is_available')) {
            $validated['is_available'] = $request->boolean('is_available');
        } else {
            unset($validated['is_available']);
        }

        if (isset($validated['display_order'])) {
            $validated['display_order'] = (int) $validated['display_order'];
        }

        $product->update($validated);

        // Sync addon groups if provided in request
        if ($request->has('addon_groups')) {
            $product->addonGroups()->sync($request->input('addon_groups', []));
        }

        // Update existing variations
        if ($request->has('existing_variations')) {
            foreach ($request->existing_variations as $varId => $vData) {
                ProductVariation::where('product_id', $product->id)
                    ->where('id', $varId)
                    ->update([
                        'name' => $vData['name'],
                        'price' => $vData['price'],
                    ]);
            }
        }

        // Add new variations
        if ($request->has('new_variations')) {
            foreach ($request->new_variations as $idx => $vData) {
                if (!empty($vData['name']) && isset($vData['price'])) {
                    ProductVariation::create([
                        'product_id' => $product->id,
                        'name' => $vData['name'],
                        'price' => $vData['price'],
                        'is_default' => false,
                        'is_active' => true,
                        'display_order' => $product->variations()->count() + 1,
                    ]);
                }
            }
        }

        AuditLog::record('product_updated', 'Product', $product->id, ['name' => $product->name]);

        return redirect()->route('admin.products.index')->with('success', "Food item '{$product->name}' updated successfully.");
    }

    public function toggleAvailability(Product $product): JsonResponse|RedirectResponse
    {
        $product->is_available = !$product->is_available;
        $product->save();

        AuditLog::record(
            action: 'product_availability_toggled',
            targetType: 'Product',
            targetId: $product->id,
            details: ['is_available' => $product->is_available]
        );

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_available' => $product->is_available,
                'message' => "Item '{$product->name}' is now " . ($product->is_available ? 'available' : 'unavailable') . '.',
            ]);
        }

        return back()->with('success', "Item '{$product->name}' is now " . ($product->is_available ? 'available' : 'unavailable') . '.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $name = $product->name;
        $product->delete();

        AuditLog::record('product_deleted', 'Product', $product->id, ['name' => $name]);

        return redirect()->route('admin.products.index')->with('success', "Food dish '{$name}' deleted successfully.");
    }

    public function destroyVariation(Product $product, ProductVariation $variation): RedirectResponse
    {
        if ($variation->product_id !== $product->id) {
            abort(403);
        }

        $variation->delete();

        return back()->with('success', 'Variation removed successfully.');
    }
}
