<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('footer_links', function (Blueprint $table) {
            $table->id();
            $table->string('section', 50)->index(); // 'quick_links', 'support_links', 'policy_links'
            $table->string('title', 150);
            $table->string('url', 255)->nullable();
            $table->foreignId('page_id')->nullable()->constrained('pages')->nullOnDelete();
            $table->string('icon', 50)->nullable();
            $table->boolean('open_in_new_tab')->default(false);
            $table->boolean('is_enabled')->default(true)->index();
            $table->integer('display_order')->default(0)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('footer_links');
    }
};
