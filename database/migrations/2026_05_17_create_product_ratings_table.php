<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->unsignedTinyInteger('rating'); // 1-5
            $table->text('review')->nullable();
            $table->boolean('is_verified')->default(false); // Verified purchase
            $table->boolean('is_approved')->default(false); // Moderation
            $table->timestamps();

            $table->unique(['product_id', 'user_id']);
            $table->index(['product_id', 'is_approved']);
            $table->index(['product_id', 'rating']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_ratings');
    }
};
