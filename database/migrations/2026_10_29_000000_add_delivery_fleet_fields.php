<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add delivery fleet fields to users
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'vehicle_type')) {
                $table->string('vehicle_type')->nullable()->after('avatar');
            }
            if (!Schema::hasColumn('users', 'vehicle_number')) {
                $table->string('vehicle_number')->nullable()->after('vehicle_type');
            }
            if (!Schema::hasColumn('users', 'is_online')) {
                $table->boolean('is_online')->default(true)->after('is_active');
            }
        });

        // 2. Add proof of delivery and delivery timestamp fields to orders
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'delivered_at')) {
                $table->dateTime('delivered_at')->nullable()->after('delivery_person_id');
            }
            if (!Schema::hasColumn('orders', 'delivery_proof_notes')) {
                $table->text('delivery_proof_notes')->nullable()->after('delivered_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'delivery_proof_notes')) {
                $table->dropColumn('delivery_proof_notes');
            }
            if (Schema::hasColumn('orders', 'delivered_at')) {
                $table->dropColumn('delivered_at');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_online')) {
                $table->dropColumn('is_online');
            }
            if (Schema::hasColumn('users', 'vehicle_number')) {
                $table->dropColumn('vehicle_number');
            }
            if (Schema::hasColumn('users', 'vehicle_type')) {
                $table->dropColumn('vehicle_type');
            }
        });
    }
};
