<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'delivery_latitude')) {
                $table->decimal('delivery_latitude', 10, 7)->nullable()->after('delivery_address_id');
            }
            if (!Schema::hasColumn('orders', 'delivery_longitude')) {
                $table->decimal('delivery_longitude', 10, 7)->nullable()->after('delivery_latitude');
            }
            if (!Schema::hasColumn('orders', 'delivery_distance_km')) {
                $table->decimal('delivery_distance_km', 8, 2)->nullable()->after('delivery_longitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'delivery_distance_km')) {
                $table->dropColumn('delivery_distance_km');
            }
            if (Schema::hasColumn('orders', 'delivery_longitude')) {
                $table->dropColumn('delivery_longitude');
            }
            if (Schema::hasColumn('orders', 'delivery_latitude')) {
                $table->dropColumn('delivery_latitude');
            }
        });
    }
};
