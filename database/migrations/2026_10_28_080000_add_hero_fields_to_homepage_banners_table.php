<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('homepage_banners', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
            $table->string('secondary_text')->nullable()->after('button_url');
            $table->string('mobile_image_path')->nullable()->after('image_path');
            $table->string('text_color')->nullable()->default('#FFFFFF')->after('bg_gradient');
            $table->dateTime('starts_at')->nullable()->after('is_active');
            $table->dateTime('ends_at')->nullable()->after('starts_at');
        });
    }

    public function down(): void
    {
        Schema::table('homepage_banners', function (Blueprint $table) {
            $table->dropColumn([
                'name',
                'secondary_text',
                'mobile_image_path',
                'text_color',
                'starts_at',
                'ends_at',
            ]);
        });
    }
};
