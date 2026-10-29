<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique()->index();
            $table->uuid('tracking_token')->unique()->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            
            // Customer Details Snapshot
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone');

            // Delivery / Pickup Details
            $table->enum('order_type', ['delivery', 'pickup'])->default('delivery');
            $table->enum('order_schedule', ['asap', 'scheduled'])->default('asap');
            $table->dateTime('scheduled_at')->nullable();
            $table->foreignId('delivery_address_id')->nullable()->constrained('addresses')->nullOnDelete();
            $table->text('delivery_address_text')->nullable();
            $table->text('delivery_instructions')->nullable();

            // Status & Notes
            $table->enum('order_status', [
                'pending_payment',
                'placed',
                'accepted',
                'preparing',
                'ready',
                'out_for_delivery',
                'delivered',
                'cancelled'
            ])->default('placed')->index();
            $table->text('cancellation_reason')->nullable();
            $table->text('internal_notes')->nullable();
            $table->text('special_instructions')->nullable();

            // Financial Calculations
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('packaging_charge', 10, 2)->default(0.00);
            $table->decimal('delivery_charge', 10, 2)->default(0.00);
            $table->decimal('tax_amount', 10, 2)->default(0.00);
            $table->decimal('total_amount', 10, 2);

            // Coupon
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
            $table->string('coupon_code')->nullable();

            // Assigned Delivery Partner
            $table->foreignId('delivery_person_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_name'); // Historical snapshot
            $table->string('product_sku')->nullable();
            $table->foreignId('variation_id')->nullable()->constrained('product_variations')->nullOnDelete();
            $table->string('variation_name')->nullable(); // Historical snapshot
            $table->decimal('unit_price', 10, 2);
            $table->integer('quantity');
            $table->decimal('line_subtotal', 10, 2);
            $table->decimal('line_tax', 10, 2)->default(0.00);
            $table->decimal('line_total', 10, 2);
            $table->text('special_instructions')->nullable();
            $table->timestamps();
        });

        Schema::create('order_item_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->foreignId('addon_id')->nullable()->constrained('addons')->nullOnDelete();
            $table->string('addon_name'); // Historical snapshot
            $table->decimal('unit_price', 10, 2);
            $table->decimal('line_total', 10, 2);
            $table->timestamps();
        });

        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->text('comment')->nullable();
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');
        Schema::dropIfExists('order_item_addons');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
