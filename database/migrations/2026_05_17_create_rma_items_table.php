<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rma_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rma_id')->constrained('rmas')->onDelete('cascade');
            $table->foreignId('order_item_id')->constrained('order_items')->onDelete('cascade');

            $table->unsignedInteger('quantity');
            $table->enum('condition', ['unopened', 'opened', 'damaged'])->nullable();
            $table->decimal('refund_amount', 10, 2);
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['rma_id', 'order_item_id']);
            $table->unique(['rma_id', 'order_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rma_items');
    }
};
