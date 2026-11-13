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
        Schema::create('banners', function (Blueprint $table) {
            // $table->bigIncrements('banner_id');
            // $table->string('image_path');
            // $table->string('title', 100)->nullable();
            // $table->string('description')->nullable();
            // $table->enum('reference_type', ['category', 'subcategory', 'brand', 'product'])->nullable();
            // $table->unsignedBigInteger('reference_id')->nullable();
            // $table->boolean('status')->default(1);
            // $table->integer('sort_order')->default(0);
            // $table->timestamps();

            $table->bigIncrements('banner_id');
            $table->string('banner_image', 255);
            $table->string('banner_title', 255)->nullable();
            $table->text('banner_subtitle')->nullable();
            $table->string('button_text', 100)->nullable()->default('Shop Now');
            $table->text('button_link')->nullable()->default(null);
            $table->enum('reference_type', ['category', 'subcategory', 'brand', 'product','link'])->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('button_position_type', 50)->nullable()->default('preset');
            $table->string('preset_position', 50)->nullable()->default('center');
            $table->integer('button_x_position')->nullable()->default(50);
            $table->integer('button_y_position')->nullable()->default(50);
            // $table->enum('navigation_type', ['category', 'product', 'link'])->nullable()->default('link');
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->boolean('banner_status')->default(true);
            $table->integer('sort_order')->nullable()->default(1);
            $table->string('button_color', 20)->nullable()->default('#fb600');
            $table->string('button_text_color', 20)->nullable()->default('#ffff');
            $table->string('button_font_size', 10)->nullable()->default('16px');
            $table->string('button_shape', 20)->nullable()->default('rounded');
            $table->timestamps();

            // Indexes for better query performance
            $table->index('banner_status');
            $table->index('sort_order');
            $table->index(['from_date', 'to_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
