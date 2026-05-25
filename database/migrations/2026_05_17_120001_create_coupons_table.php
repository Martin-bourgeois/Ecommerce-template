<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('promotion_id');
            $table->string('code')->unique();

            // Usage limits
            $table->unsignedBigInteger('usage_limit')->nullable();
            $table->unsignedBigInteger('usage_count')->default(0);
            $table->unsignedBigInteger('per_customer_limit')->nullable();

            // Dates
            $table->dateTime('valid_from')->nullable();
            $table->dateTime('valid_until')->nullable();

            $table->timestamps();

            // Foreign key
            $table->foreign('promotion_id')
                ->references('id')
                ->on('promotions')
                ->cascadeOnDelete();

            // Indexes
            $table->index('code');
            $table->index('promotion_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
