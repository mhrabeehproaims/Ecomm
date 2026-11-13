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
        Schema::create('tax_schedules', function (Blueprint $table) {
            $table->bigIncrements('tax_id');
            $table->string('tax_name', 50);
            $table->decimal('tax_percentage', 5,2);
            $table->decimal('cgst', 5,2)->nullable();
            $table->decimal('sgst', 5,2)->nullable();
            $table->decimal('igst', 5,2)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taxes');
    }
};
