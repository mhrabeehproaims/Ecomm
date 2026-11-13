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
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('order_id');
            $table->unsignedBigInteger('customer_id');
            $table->decimal('total_amount', 10, 2)->comment('with discount');
            $table->decimal('discount', 10, 2)->default(0)->comment('Discount Amount');
            $table->decimal('total_tax_amount', 10, 2)->nullable();
            $table->enum('status', [
                'ordered',
                'rejected',
                'confirmed',
                'processing',
                'despatched',
                'ontransist',
                'delivered',
                'reissued',
            ])->default('ordered');
            $table->string('payment_method')->nullable();
            $table->string('payment_status')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreign('customer_id')->references('customer_id')->on('customers')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
