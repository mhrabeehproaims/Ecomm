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
            // Use descriptive column name and set up relationship
            $table->unsignedBigInteger('delivery_agent_id')->nullable()->after('status');
            $table->string('delivery_tracking_id', 100)->nullable()->after('delivery_agent_id');
            $table->date('delivery_date')->nullable()->after('delivery_tracking_id');

            // Optional: Add foreign key constraint (if you have a delivery_partners table)
            $table->foreign('delivery_agent_id')
                ->references('partner_id')
                ->on('delivery_partners')
                ->onDelete('set null');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
};
