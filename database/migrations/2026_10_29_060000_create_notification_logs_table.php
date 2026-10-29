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
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('role', 30)->nullable()->index(); // customer, rider, admin
            $table->string('event_type', 60)->index();
            $table->string('title');
            $table->text('body');
            $table->json('data')->nullable();
            $table->string('device_token', 500)->nullable();
            $table->string('status', 20)->default('pending')->index(); // sent, failed, pending
            $table->text('error_message')->nullable();
            $table->string('fcm_message_id')->nullable();
            $table->unsignedInteger('retry_count')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
