<?php

declare(strict_types=1);

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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('sku')->unique();
            $table->string('name')->nullable();
            $table->integer('price'); // In cents
            $table->integer('cost')->nullable(); // In cents
            $table->integer('stock')->default(0);
            $table->integer('reserved_stock')->default(0);
            $table->integer('weight')->nullable(); // In grams
            $table->string('barcode')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // Foreign keys
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            // Indexes
            $table->index('product_id');
            $table->index('sku');
            $table->index('is_active');
            $table->index(['product_id', 'is_active']);
            $table->index(['stock', 'reserved_stock']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
