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
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('coupon_id')->nullable()->after('total_tax_amount');
            $table->string('coupon_code', 100)->nullable()->after('coupon_id');
            $table->decimal('coupon_discount')->nullable()->after('coupon_code')->comment('Discount Value (% or rupee)');
            $table->string('coupon_discount_type')->nullable()->after('coupon_discount');
            $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('coupon_id');
            $table->dropColumn('coupon_code');
            $table->dropColumn('coupon_discount');
            $table->dropColumn('coupon_discount_type');
        });
    }
};
