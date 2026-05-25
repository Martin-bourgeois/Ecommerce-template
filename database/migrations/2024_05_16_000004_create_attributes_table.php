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
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('type', ['select', 'multiselect', 'text', 'boolean'])->default('select');
            $table->boolean('is_filterable')->default(true);
            $table->boolean('is_searchable')->default(false);
            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // Indexes
            $table->index('type');
            $table->index('sort_order');
        });

        Schema::create('attribute_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attribute_id');
            $table->string('label');
            $table->string('value')->unique();
            $table->string('color')->nullable(); // For color-like attributes
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // Foreign keys
            $table->foreign('attribute_id')->references('id')->on('attributes')->onDelete('cascade');

            // Indexes
            $table->index(['attribute_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attribute_options');
        Schema::dropIfExists('attributes');
    }
};
