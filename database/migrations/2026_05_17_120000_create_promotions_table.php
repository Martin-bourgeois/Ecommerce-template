<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            // Type and target
            $table->string('type'); // percentage, fixed_amount, free_shipping
            $table->string('target'); // order, product, category
            $table->decimal('value', 10, 2); // For percentage/fixed amount

            // Conditions (JSON)
            $table->json('conditions')->default('{}');

            // Rules
            $table->boolean('is_stackable')->default(false);
            $table->boolean('is_active')->default(true);

            // Dates
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();

            // Usage tracking
            $table->unsignedBigInteger('usage_limit')->nullable();
            $table->unsignedBigInteger('usage_count')->default(0);

            // Priority for non-stackable
            $table->integer('priority')->default(0);

            $table->timestamps();

            // Indexes
            $table->index('type');
            $table->index('target');
            $table->index('is_active');
            $table->index('is_stackable');
            $table->index(['starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
