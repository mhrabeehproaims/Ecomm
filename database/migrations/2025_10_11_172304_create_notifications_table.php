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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            // notification type (order, stock, payment, etc.)
            $table->string('type')->nullable();

            // title and message for display
            $table->string('title')->nullable();
            $table->text('message')->nullable();

            // optional reference to another table (like order_id, product_id, etc.)
            $table->string('notifiable_type')->nullable(); // e.g. App\Models\Order
            $table->unsignedBigInteger('notifiable_id')->nullable()->comment('id according to notifiable_type');

            // JSON payload (extra data if needed)
            $table->json('data')->nullable();

            // whether the user has read the notification
            $table->boolean('is_read')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
