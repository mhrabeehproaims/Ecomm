<?php
// database/migrations/2025_10_02_000001_create_order_status_logs_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_status_logs', function (Blueprint $table) {
            $table->bigIncrements('log_id');
            $table->unsignedBigInteger('order_id');
            $table->foreign('order_id')->references('order_id')->on('orders')->onDelete('cascade');
            $table->enum('status', [
                'ordered',
                'rejected',
                'confirmed',
                'processing',
                'despatched',
                'ontransit',
                'delivered',
                'reissued',
                'requested',
                'accepted',
                'picked_up',
                'collected',
                'refunded',
                'replaced'
            ])->nullable();
            $table->unsignedBigInteger('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamp('changed_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_logs');
    }
};
