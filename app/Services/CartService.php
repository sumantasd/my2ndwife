<?php

namespace App\Services;

use App\Models\Addon;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\RestaurantSetting;
use Illuminate\Support\Facades\Session;

class CartService
{
    private const SESSION_KEY = 'm2w_cart_items';
    private const COUPON_KEY = 'm2w_cart_coupon';

    public function getRawCart(): array
    {
        return Session::get(self::SESSION_KEY, []);
    }

    public function add(int $productId, int $quantity = 1, ?int $variationId = null, array $addonIds = [], ?string $instructions = null): array
    {
        $product = Product::with(['variations', 'addonGroups.addons'])->find($productId);
        if (!$product || !$product->is_available) {
            return ['success' => false, 'message' => 'This food item is currently unavailable.'];
        }

        // Validate variation if product has active variations
        $selectedVariation = null;
        if ($product->activeVariations->isNotEmpty()) {
            if ($variationId) {
                $selectedVariation = $product->activeVariations->firstWhere('id', $variationId);
            }
            if (!$selectedVariation) {
                $selectedVariation = $product->activeVariations->firstWhere('is_default', true) ?? $product->activeVariations->first();
                $variationId = $selectedVariation?->id;
            }
        } else {
            $variationId = null;
        }

        // Validate addons belong to product's addon groups
        $validAddonIds = [];
        if (!empty($addonIds)) {
            $allowedAddonIds = $product->addonGroups
                ->flatMap(fn($g) => $g->activeAddons->pluck('id'))
                ->toArray();
            $validAddonIds = array_values(array_intersect($addonIds, $allowedAddonIds));
            sort($validAddonIds);
        }

        $cart = $this->getRawCart();
        $itemKey = $this->generateItemKey($productId, $variationId, $validAddonIds);

        if (isset($cart[$itemKey])) {
            $cart[$itemKey]['quantity'] += $quantity;
            if ($instructions) {
                $cart[$itemKey]['instructions'] = $instructions;
            }
        } else {
            $cart[$itemKey] = [
                'product_id' => $productId,
                'variation_id' => $variationId,
                'addons' => $validAddonIds,
                'quantity' => max(1, $quantity),
                'instructions' => $instructions ? strip_tags(trim($instructions)) : null,
            ];
        }

        Session::put(self::SESSION_KEY, $cart);

        $totals = $this->calculateTotals();

        return [
            'success' => true,
            'message' => "{$product->name} added to cart!",
            'product_name' => $product->name,
            'cart_count' => $this->count(),
            'cart_subtotal' => $totals['subtotal'] ?? 0,
        ];
    }

    public function updateQuantity(string $itemKey, int $quantity): array
    {
        $cart = $this->getRawCart();

        if (!isset($cart[$itemKey])) {
            return ['success' => false, 'message' => 'Item not found in cart.'];
        }

        if ($quantity <= 0) {
            unset($cart[$itemKey]);
        } else {
            $cart[$itemKey]['quantity'] = min(20, $quantity); // Reasonable safety cap
        }

        Session::put(self::SESSION_KEY, $cart);

        return ['success' => true, 'cart_count' => $this->count()];
    }

    public function removeItem(string $itemKey): array
    {
        $cart = $this->getRawCart();
        if (isset($cart[$itemKey])) {
            unset($cart[$itemKey]);
            Session::put(self::SESSION_KEY, $cart);
        }

        return ['success' => true, 'cart_count' => $this->count()];
    }

    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
        Session::forget(self::COUPON_KEY);
    }

    public function count(): int
    {
        $cart = $this->getRawCart();
        return array_sum(array_column($cart, 'quantity'));
    }

    public function applyCoupon(string $code, ?int $userId = null): array
    {
        $code = strtoupper(trim($code));
        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            return ['success' => false, 'message' => 'Invalid promo coupon code.'];
        }

        $totals = $this->calculateTotals(null);
        $check = $coupon->isValidFor($totals['subtotal'], $userId);

        if (!$check['valid']) {
            return ['success' => false, 'message' => $check['message']];
        }

        Session::put(self::COUPON_KEY, $coupon->code);

        return ['success' => true, 'message' => 'Promo coupon applied! You saved ' . RestaurantSetting::get('currency_symbol', '₹') . number_format($coupon->calculateDiscount($totals['subtotal']), 2)];
    }

    public function removeCoupon(): void
    {
        Session::forget(self::COUPON_KEY);
    }

    public function getAppliedCoupon(): ?Coupon
    {
        $code = Session::get(self::COUPON_KEY);
        if (!$code) {
            return null;
        }

        return Coupon::where('code', $code)->where('is_active', true)->first();
    }

    /**
     * Reconstruct and validate all items with fresh database records.
     */
    public function getDetailedItems(): array
    {
        $rawCart = $this->getRawCart();
        $detailed = [];

        foreach ($rawCart as $key => $raw) {
            $product = Product::with(['category'])->find($raw['product_id']);
            if (!$product || !$product->is_available) {
                continue;
            }

            $variation = null;
            if (!empty($raw['variation_id'])) {
                $variation = ProductVariation::where('product_id', $product->id)->find($raw['variation_id']);
            }

            // Base price: variation price or product current price
            $baseUnitPrice = $variation ? (float) $variation->price : (float) $product->current_price;

            // Addons
            $addons = [];
            $addonsTotal = 0.00;
            if (!empty($raw['addons'])) {
                $addonRecords = Addon::whereIn('id', $raw['addons'])->where('is_active', true)->get();
                foreach ($addonRecords as $addon) {
                    $addons[] = [
                        'id' => $addon->id,
                        'name' => $addon->name,
                        'price' => (float) $addon->price,
                    ];
                    $addonsTotal += (float) $addon->price;
                }
            }

            $itemUnitPrice = $baseUnitPrice + $addonsTotal;
            $quantity = (int) $raw['quantity'];
            $lineSubtotal = round($itemUnitPrice * $quantity, 2);

            $detailed[$key] = [
                'key' => $key,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_slug' => $product->slug,
                'product_image' => $product->main_image_url,
                'is_veg' => $product->is_veg,
                'sku' => $product->sku,
                'variation_id' => $variation?->id,
                'variation_name' => $variation?->name,
                'base_unit_price' => $baseUnitPrice,
                'addons' => $addons,
                'addons_total' => $addonsTotal,
                'unit_price' => $itemUnitPrice,
                'quantity' => $quantity,
                'line_subtotal' => $lineSubtotal,
                'instructions' => $raw['instructions'] ?? null,
            ];
        }

        return $detailed;
    }

    public function calculateTotals(?string $couponCode = null, string $orderType = 'delivery', ?int $userId = null, ?float $distanceKm = null): array
    {
        $items = $this->getDetailedItems();
        $subtotal = 0.00;

        foreach ($items as $item) {
            $subtotal += $item['line_subtotal'];
        }

        $subtotal = round($subtotal, 2);

        // Coupon calculation
        $discountAmount = 0.00;
        $appliedCoupon = null;
        $codeToUse = $couponCode ?: Session::get(self::COUPON_KEY);

        if ($codeToUse) {
            $coupon = Coupon::where('code', $codeToUse)->first();
            if ($coupon) {
                $check = $coupon->isValidFor($subtotal, $userId);
                if ($check['valid']) {
                    $appliedCoupon = $coupon;
                    $discountAmount = $coupon->calculateDiscount($subtotal);
                } else {
                    // Auto-remove invalid coupon from session
                    $this->removeCoupon();
                }
            }
        }

        // Delivery charge using DeliveryService
        $deliveryService = app(DeliveryService::class);
        $freeDeliveryThreshold = (float) RestaurantSetting::get('free_delivery_threshold', 499.00);

        $deliveryCharge = 0.00;
        if ($orderType === 'delivery') {
            $deliveryCharge = $deliveryService->calculateDeliveryFee($subtotal, $distanceKm);
        }

        // Packaging charge
        $packagingCharge = $subtotal > 0 ? (float) RestaurantSetting::get('packaging_charge', 25.00) : 0.00;

        // Taxes
        $taxEnabled = (bool) RestaurantSetting::get('tax_enabled', true);
        $taxRate = $taxEnabled ? (float) RestaurantSetting::get('tax_rate_percentage', 5.00) : 0.00;
        $taxableAmount = max(0.00, $subtotal - $discountAmount + $packagingCharge);
        $taxAmount = $taxEnabled ? round(($taxableAmount * $taxRate) / 100, 2) : 0.00;

        $totalAmount = max(0.00, round(($subtotal - $discountAmount) + $deliveryCharge + $packagingCharge + $taxAmount, 2));

        $minOrderAmount = (float) RestaurantSetting::get('minimum_order_amount', 199.00);
        $isEligible = $subtotal >= $minOrderAmount;

        return [
            'items' => $items,
            'items_count' => array_sum(array_column($items, 'quantity')),
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'coupon' => $appliedCoupon,
            'coupon_code' => $appliedCoupon?->code,
            'delivery_charge' => $deliveryCharge,
            'is_free_delivery' => $deliveryCharge === 0.00 && $subtotal > 0 && $orderType === 'delivery',
            'free_delivery_threshold' => $freeDeliveryThreshold,
            'free_delivery_remaining' => max(0.00, $freeDeliveryThreshold - $subtotal),
            'packaging_charge' => $packagingCharge,
            'tax_enabled' => $taxEnabled,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'min_order_amount' => $minOrderAmount,
            'is_min_order_met' => $isEligible,
            'min_order_remaining' => max(0.00, $minOrderAmount - $subtotal),
            'order_type' => $orderType,
            'distance_km' => $distanceKm,
        ];
    }

    private function generateItemKey(int $productId, ?int $variationId, array $addonIds): string
    {
        sort($addonIds);
        return md5($productId . '_' . ($variationId ?? '0') . '_' . implode(',', $addonIds));
    }
}
