<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('storefront_order_documents', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('order_id');
            $t->unsignedBigInteger('invoice_id');
            $t->string('kind', 20);
            $t->string('path')->unique();
            $t->char('sha256', 64);
            $t->longText('snapshot')->nullable();
            $t->string('source', 30);
            $t->timestamps();
            $t->unique(['invoice_id', 'kind']);
        });
        Schema::table('storefront_webhook_events', function (Blueprint $t) {
            $t->string('provider_order_id', 100)->nullable();
            $t->string('provider_payment_id', 100)->nullable();
            $t->string('provider_refund_id', 100)->nullable();
            $t->unsignedInteger('attempts')->default(0);
            $t->timestamp('next_attempt_at')->nullable()->index();
            $t->string('lease_token', 36)->nullable();
            $t->timestamp('lease_expires_at')->nullable();
            $t->string('last_error', 100)->nullable();
        });
        Schema::table('storefront_payment_attempts', function (Blueprint $t) {
            $t->unsignedBigInteger('refunded_minor')->default(0);
            $t->timestamp('next_reconcile_at')->nullable()->index();
            $t->string('reconcile_lease', 36)->nullable();
            $t->timestamp('reconcile_lease_expires_at')->nullable();
            $t->unsignedInteger('reconcile_failures')->default(0);
        });
        Schema::create('storefront_financial_entries', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('order_id')->index();
            $t->string('reference', 150)->unique();
            $t->string('kind', 30);
            $t->unsignedBigInteger('amount_minor');
            $t->string('currency', 3)->default('INR');
            $t->unsignedBigInteger('actor_id')->nullable();
            $t->string('evidence', 200)->nullable();
            $t->timestamps();
            $t->unique(['order_id', 'kind', 'evidence'], 'storefront_unique_receipt');
        });
        Schema::create('storefront_provider_refunds', function (Blueprint $t) {
            $t->string('provider_refund_id', 100)->primary();
            $t->unsignedBigInteger('order_id')->index();
            $t->string('provider_payment_id', 100);
            $t->unsignedBigInteger('amount_minor');
            $t->string('state', 20);
            $t->timestamps();
        });
        Schema::table('storefront_refunds', function (Blueprint $t) {
            $t->unsignedBigInteger('return_id')->nullable()->change();
            $t->unsignedBigInteger('late_capture_order_id')->nullable()->unique();
        });
        Schema::table('storefront_checkouts', fn (Blueprint $t) => $t->timestamp('next_expiry_attempt_at')->nullable()->index());
        Schema::table('storefront_outbox', function (Blueprint $t) {
            $t->timestamp('next_attempt_at')->nullable()->index();
            $t->string('lease_token', 36)->nullable();
        });
        Schema::create('storefront_operational_alerts', function (Blueprint $t) {
            $t->char('id', 64)->primary();
            $t->string('code', 60);
            $t->string('reference', 150);
            $t->unsignedInteger('occurrences')->default(1);
            $t->timestamp('resolved_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Financial evidence must not be dropped automatically. Use an explicitly reviewed forward recovery.');
    }
};
