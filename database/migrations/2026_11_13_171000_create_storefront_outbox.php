<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('storefront_outbox', function (Blueprint $table) {
            $table->char('id', 64)->primary();
            $table->string('kind', 40);
            $table->json('payload');
            $table->string('state', 20)->default('pending')->index();
            $table->unsignedInteger('attempts')->default(0);
            $table->string('last_error', 200)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('storefront_outbox');
    }
};
