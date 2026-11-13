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
        Schema::create('sales_return_items', function (Blueprint $table) {
            $table->bigIncrements('return_item_id');

             $table->unsignedBigInteger('sales_return_id');
            $table->foreign('sales_return_id')->references('return_id')->on('sales_returns')->onDelete('cascade');

             $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('product_id')->on('products')->onDelete('cascade');
            
            $table->unsignedBigInteger('order_item_id');
            $table->foreign('order_item_id')->references('order_item_id')->on('order_items')->onDelete('cascade');

            $table->decimal('quantity', 10, 2);
            $table->decimal('price', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_return_items');
    }
};
