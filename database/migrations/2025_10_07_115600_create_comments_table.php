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
        Schema::create('comments', function (Blueprint $table) {
            $table->bigIncrements('comment_id');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('order_item_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->text('comment');
            $table->tinyInteger('rating')->nullable();
            $table->foreign('customer_id')->references('customer_id')->on('customers')->onDelete('cascade');
            $table->foreign('order_item_id')->references('order_item_id')->on('order_items')->onDelete('cascade');
            $table->foreign('parent_id')->references('comment_id')->on('comments')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
