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
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('product_id');
            $table->string('product_name', 150)->index();
            $table->text('description')->nullable();
            $table->text('health_benefit')->nullable();
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('sub_category_id')->nullable();
            $table->integer('stock')->default(0);
            $table->integer('minimum_stock')->default(0);
            $table->unsignedBigInteger('brand_id');
            $table->unsignedBigInteger('size_id')->nullable();
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->unsignedBigInteger('package_type_id')->nullable();
            $table->decimal('mrp', 8, 2);
            $table->decimal('retail', 8, 2)->nullable();
            $table->unsignedBigInteger('tax_schedule_id')->nullable();
            $table->decimal('discount', 8, 2)->nullable();
            $table->enum('discount_type', ['rupee', 'percentage'])->nullable();
            $table->timestamps();

            $table->foreign('category_id')->references('categ_id')->on('categories')->onDelete('cascade');
            $table->foreign('sub_category_id')->references('subCat_id')->on('sub_categories')->onDelete('set null');
            $table->foreign('brand_id')->references('brand_id')->on('brands')->onDelete('cascade');
            $table->foreign('size_id')->references('size_id')->on('sizes')->onDelete('set null');
            $table->foreign('unit_id')->references('unit_id')->on('units')->onDelete('set null');
            $table->foreign('package_type_id')->references('package_id')->on('packaging_types')->onDelete('set null');
            $table->foreign('tax_schedule_id')->references('tax_id')->on('tax_schedules')->onDelete('set null');

            $table->index('category_id');
            $table->index('sub_category_id');
            $table->index('brand_id');
            $table->index(['category_id', 'brand_id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
