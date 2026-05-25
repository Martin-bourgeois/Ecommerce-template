<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotion_usages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->unsignedBigInteger('promotion_id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('used_at');

            // Foreign keys
            $table->foreign('coupon_id')
                ->references('id')
                ->on('coupons')
                ->nullableOnDelete();

            $table->foreign('promotion_id')
                ->references('id')
                ->on('promotions')
                ->cascadeOnDelete();

            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->cascadeOnDelete();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            // Indexes
            $table->index('coupon_id');
            $table->index('promotion_id');
            $table->index('order_id');
            $table->index('user_id');
            $table->index('used_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_usages');
    }
};
