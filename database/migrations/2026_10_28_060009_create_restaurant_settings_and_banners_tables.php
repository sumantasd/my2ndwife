<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->string('type')->default('string'); // string, json, boolean, integer, float
            $table->string('group')->default('general'); // general, delivery, payment, branding
            $table->timestamps();
        });

        Schema::create('homepage_banners', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('badge_text')->nullable();
            $table->string('button_text')->nullable()->default('Order Now');
            $table->string('button_url')->nullable()->default('/menu');
            $table->string('image_path')->nullable();
            $table->string('bg_gradient')->nullable()->default('from-pink-500 to-rose-600');
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action'); // e.g. updated_order_status, created_product
            $table->string('target_type')->nullable(); // Order, Product, Category, Setting
            $table->unsignedBigInteger('target_id')->nullable();
            $table->json('details')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('homepage_banners');
        Schema::dropIfExists('restaurant_settings');
    }
};
