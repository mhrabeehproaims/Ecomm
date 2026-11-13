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
        Schema::create('sales_returns', function (Blueprint $table) {
            $table->bigIncrements('return_id');
            $table->unsignedBigInteger('order_id')->comment('sale id');
            $table->foreign('order_id')->references('order_id')->on('orders')->onDelete('cascade');
            $table->unsignedBigInteger('reissued_order_id')->nullable();
            $table->foreign('reissued_order_id')->references('order_id')->on('orders')->onDelete('cascade');
            $table->unsignedBigInteger('customer_id');
            $table->foreign('customer_id')->references('customer_id')->on('customers')->onDelete('cascade');
            $table->dateTime('return_date');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->enum('status', [
                'requested',     // requested by customer
                'accepted',    // approved by admin
                'rejected',    // rejected request
                'picked_up',  // picked up or being processed
                'collected',    // product received
                'refunded',   // checked for quality/condition
                'replaced',    // refund processed, return complete
            ])->default('requested');
            $table->text('notes')->nullable();
            $table->text('reject_reason')->nullable();
            $table->enum('return_type',['refund', 'replacement'])->default('refund');
            $table->string('return_reason')->nullable();
            $table->enum('product_condition', ['damaged', 'unused', 'opened', 'other'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_returns');
    }
};
