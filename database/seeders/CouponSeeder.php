<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            [
                'code' => 'TASTE20',
                'type' => 'percentage',
                'discount_amount' => 20.00,
                'min_order_amount' => 199.00,
                'max_discount_amount' => 100.00,
                'usage_limit' => 1000,
                'per_customer_limit' => 5,
                'start_date' => Carbon::now()->subDays(5),
                'expires_at' => Carbon::now()->addMonths(6),
                'is_active' => true,
            ],
            [
                'code' => 'WELCOME50',
                'type' => 'percentage',
                'discount_amount' => 50.00,
                'min_order_amount' => 299.00,
                'max_discount_amount' => 150.00,
                'usage_limit' => 500,
                'per_customer_limit' => 1,
                'start_date' => Carbon::now()->subDays(5),
                'expires_at' => Carbon::now()->addMonths(6),
                'is_active' => true,
            ],
            [
                'code' => 'FEAST100',
                'type' => 'fixed',
                'discount_amount' => 100.00,
                'min_order_amount' => 499.00,
                'max_discount_amount' => 100.00,
                'usage_limit' => 300,
                'per_customer_limit' => 2,
                'start_date' => Carbon::now()->subDays(5),
                'expires_at' => Carbon::now()->addMonths(6),
                'is_active' => true,
            ],
        ];

        foreach ($coupons as $c) {
            Coupon::create($c);
        }
    }
}
