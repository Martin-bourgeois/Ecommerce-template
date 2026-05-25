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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->enum('type', ['configurable', 'simple'])->default('simple');
            $table->enum('status', ['draft', 'active', 'discontinued'])->default('draft');
            $table->integer('price')->nullable(); // In cents
            $table->integer('cost')->nullable(); // In cents
            $table->integer('weight')->nullable(); // In grams
            $table->string('sku')->nullable()->unique(); // For simple products
            $table->string('barcode')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('brand')->nullable();
            $table->text('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->text('seo_keywords')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->integer('view_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');

            // Indexes
            $table->index('category_id');
            $table->index('status');
            $table->index('type');
            $table->index('slug');
            $table->index(['status', 'is_featured']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
