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
        Schema::create('order_items', function (Blueprint $table) {
            $table->bigIncrements('order_item_id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity');
            $table->decimal('price', 10, 2)->comment('single item`s Net price'); // price at checkout time
            $table->decimal('total', 10, 2)->comment('including tax'); // quantity * price

            $table->decimal('tax_percent', 10, 2)->default(0);
            $table->decimal('cgst_percent', 10, 2)->default(0);
            $table->decimal('sgst_percent', 10, 2)->default(0);
            $table->decimal('igst_percent', 10, 2)->default(0);

            $table->decimal('amount_wo_tax', 10, 2)->default(0);
            $table->decimal('cgst_amount', 10, 2)->default(0);
            $table->decimal('sgst_amount', 10, 2)->default(0);
            $table->decimal('igst_amount', 10, 2)->default(0);

            $table->foreign('order_id')->references('order_id')->on('orders')->onDelete('cascade');
            $table->foreign('product_id')->references('product_id')->on('products')->onDelete('cascade');

            $table->timestamps();
        });

        // Schema::create('payments', function (Blueprint $table) {
        //     $table->bigIncrements('payment_id');
        //     $table->unsignedBigInteger('order_id');
        //     $table->foreign('order_id')->references('order_id')->on('orders')->onDelete('cascade');

        //     $table->string('gateway')->nullable();
        //     // e.g. stripe, razorpay, paypal

        //     $table->string('transaction_id')->nullable();
        //     // returned by payment gateway

        //     $table->decimal('amount', 10, 2);
        //     $table->string('currency', 10)->default('INR');

        //     $table->string('status')->default('pending');
        //     // pending, success, failed, refunded

        //     $table->json('response')->nullable();
        //     // raw response from payment gateway for debugging

        //     $table->timestamps();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('payments');
    }
};
