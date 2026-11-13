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
        Schema::create('offer_zones', function (Blueprint $table) {
            $table->id();
            $table->string('offer_type');
            $table->string('title')->nullable();
            $table->text('description')->nullable();

            $table->string('discount_type')->nullable();
            $table->decimal('discount_value', 12, 2)->nullable();
            $table->string('discount_from')->nullable();

            // MULTIPLE SELECT AS JSON
            $table->json('category_ids')->nullable();
            $table->json('subcategory_ids')->nullable();
            $table->json('brand_ids')->nullable();

            $table->json('applicable_product_ids')->nullable();
            $table->longText('product_details')->nullable();

            $table->integer('buy_qty')->nullable();
            $table->integer('get_qty')->nullable();

            $table->boolean('free_shipping')->default(false);

            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();

            $table->tinyInteger('status')->default(1);

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offer_zones');
    }
};
