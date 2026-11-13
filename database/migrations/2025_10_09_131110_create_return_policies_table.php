<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('return_policies', function (Blueprint $table) {
            $table->bigIncrements('policy_id');
            $table->string('title'); // e.g. "Standard 7-Day Return"
            $table->integer('return_days')->default(7); // number of days allowed for return
            $table->enum('return_type', ['refund', 'exchange', 'refund_or_exchange'])->default('refund_or_exchange');
            $table->boolean('is_active')->default(true);
            $table->text('conditions')->nullable(); // detailed return conditions
            $table->unsignedBigInteger('created_by')->nullable(); // admin/user who created policy
            $table->timestamps();

            // Optional foreign key if you have users or admins table
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_policies');
    }
};


