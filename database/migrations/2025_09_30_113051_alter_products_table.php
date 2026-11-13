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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('wholesale', 8, 2)->nullable()->after('retail');
            $table->decimal('special_price', 8, 2)->nullable()->after('wholesale');
            $table->string('primary_price', 20)->after('wholesale')->default('mrp');
            // $table->unsignedBigInteger('weight_id')->after('unit_id')->nullable();
            $table->decimal('weight_id')->after('unit_id')->nullable();
            $table->string('hsn_code', 20)->after('discount_type')->nullable();
            $table->string('package_weight',50)->after('hsn_code')->nullable(); 

            // $table->foreign('weight_id')->references('weight_id')->on('weights')->onDelete('set null');
            $table->index('weight_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('wholesale');
            $table->dropColumn('special_price');
            $table->dropColumn('primary_price');
            $table->dropColumn('product_weight');
        });
    }
};
