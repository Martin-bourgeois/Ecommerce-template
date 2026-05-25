<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('loyalty_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->string('type'); // earned, spent, refunded, expired, bonus
            $table->bigInteger('points'); // Can be negative for spent/refunded
            $table->text('description')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->timestamp('created_at');

            // Foreign keys
            $table->foreign('account_id')
                ->references('id')
                ->on('loyalty_accounts')
                ->cascadeOnDelete();

            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->nullableOnDelete();

            // Indexes
            $table->index('account_id');
            $table->index('type');
            $table->index('order_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_transactions');
    }
};
