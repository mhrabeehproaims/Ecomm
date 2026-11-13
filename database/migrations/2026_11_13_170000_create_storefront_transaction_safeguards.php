<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('storefront_quotes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('customer_id')->index();
            $table->char('fingerprint', 64);
            $table->json('payload');
            $table->timestamp('expires_at');
            $table->unsignedBigInteger('consumed_order_id')->nullable()->unique();
            $table->timestamps();
        });
        Schema::create('storefront_checkouts', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')->primary();
            $table->unsignedBigInteger('customer_id')->index();
            $table->uuid('quote_id')->unique();
            $table->uuid('idempotency_key');
            $table->string('state', 30)->index();
            $table->timestamp('expires_at')->index();
            $table->json('address_snapshot');
            $table->json('breakdown');
            $table->unsignedBigInteger('coupon_id')->nullable()->index();
            $table->timestamp('committed_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
            $table->unique(['customer_id', 'idempotency_key'], 'storefront_checkout_idempotency');
            $table->foreign('order_id')->references('order_id')->on('orders');
        });
        Schema::create('storefront_payment_attempts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('order_id')->unique();
            $table->unsignedBigInteger('legacy_payment_id')->nullable()->unique();
            $table->string('provider_order_id', 100)->nullable()->unique();
            $table->string('provider_payment_id', 100)->nullable()->unique();
            $table->unsignedBigInteger('amount_minor');
            $table->string('currency', 3)->default('INR');
            $table->string('state', 30);
            $table->string('failure_code', 100)->nullable();
            $table->timestamps();
            $table->foreign('order_id')->references('order_id')->on('orders');
        });
        Schema::create('storefront_webhook_events', function (Blueprint $table) {
            $table->string('event_id', 150)->primary();
            $table->char('body_hash', 64)->unique();
            $table->string('event_type', 100);
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
        Schema::create('storefront_payment_events', function (Blueprint $table) {
            $table->char('event_key', 64)->primary();
            $table->uuid('attempt_id')->index();
            $table->string('provider_payment_id', 100);
            $table->string('state', 30);
            $table->string('source', 30);
            $table->unsignedBigInteger('amount_minor');
            $table->timestamps();
        });
        Schema::create('storefront_return_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('return_id')->primary();
            $table->unsignedBigInteger('customer_id');
            $table->uuid('request_key');
            $table->char('fingerprint', 64);
            $table->timestamps();
            $table->unique(['customer_id', 'request_key'], 'storefront_return_idempotency');
        });
        Schema::create('storefront_refunds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('return_id')->unique();
            $table->unsignedBigInteger('order_id');
            $table->string('provider_payment_id', 100);
            $table->string('provider_refund_id', 100)->nullable()->unique();
            $table->unsignedBigInteger('amount_minor');
            $table->string('state', 30);
            $table->timestamps();
        });
        Schema::table('coupon_usages', function (Blueprint $table) {
            $table->unsignedBigInteger('discount_amount_minor')->nullable();
        });
    }

    public function down(): void
    {
        // Rollback is intentionally explicit; never used by the local test setup.
        Schema::table('coupon_usages', fn (Blueprint $table) => $table->dropColumn('discount_amount_minor'));
        foreach (['storefront_refunds', 'storefront_return_requests', 'storefront_payment_events', 'storefront_webhook_events', 'storefront_payment_attempts', 'storefront_checkouts', 'storefront_quotes'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
