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
        Schema::create('stock_management', function (Blueprint $table) {
            $table->bigIncrements('stock_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity')->default(0);
            $table->string('stock_status')->default('in_stock');
            $table->text('notes')->nullable(); // Any notes
            $table->foreign('product_id')->references('product_id')->on('products')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_management');
    }
};
