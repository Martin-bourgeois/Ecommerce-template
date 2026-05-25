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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating'); // 1-5
            $table->string('title', 255);
            $table->text('comment');
            $table->boolean('is_approved')->default(false);
            $table->boolean('verified_purchase')->default(true);
            $table->unsignedInteger('helpful_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            // Unique constraint: one review per product per order
            $table->unique(['product_id', 'order_id']);

            // Indexes
            $table->index('user_id');
            $table->index('is_approved');
            $table->index('rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
