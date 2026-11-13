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
        Schema::create('sub_categories', function (Blueprint $table) {
            $table->bigIncrements('subCat_id');
            $table->string('image', 200)->nullable();
            $table->unsignedBigInteger('cat_id');
            $table->string('subCat_name', 150);
            $table->text('subcateg_desc')->nullable();
            $table->integer('return_days')->default(0);
            $table->tinyInteger('status')->default(1);

            $table->foreign('cat_id')->references('categ_id')->on('categories')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_categories');
    }
};
