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
        Schema::create('delivery_agent_pincodes', function (Blueprint $table) {
            $table->id('pincode_id');
            $table->unsignedBigInteger('delivery_agent_id');
            $table->string('pincode', 10);
            $table->foreign('delivery_agent_id')->references('partner_id')->on('delivery_partners')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['delivery_agent_id', 'pincode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_agent_pincodes');
    }
};
