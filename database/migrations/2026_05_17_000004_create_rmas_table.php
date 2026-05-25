<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rmas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');

            $table->string('rma_number', 50)->unique()->nullable();
            $table->enum('status', ['requested', 'approved', 'received', 'inspected', 'refunded', 'rejected'])->default('requested');
            $table->enum('reason', ['defective', 'wrong_item', 'not_as_described', 'changed_mind', 'other']);

            $table->text('customer_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->timestamp('refunded_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['order_id', 'status']);
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rmas');
    }
};
