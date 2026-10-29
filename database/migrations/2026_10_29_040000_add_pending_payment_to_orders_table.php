<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // For MySQL, alter the enum column to include 'pending_payment'
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN order_status ENUM('pending_payment','placed','accepted','preparing','ready','out_for_delivery','delivered','cancelled') NOT NULL DEFAULT 'placed'");
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN order_status ENUM('placed','accepted','preparing','ready','out_for_delivery','delivered','cancelled') NOT NULL DEFAULT 'placed'");
        }
    }
};
