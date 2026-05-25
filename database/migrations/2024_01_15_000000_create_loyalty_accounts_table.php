<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('loyalty_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->unsignedBigInteger('points_balance')->default(0);
            $table->unsignedBigInteger('total_earned')->default(0);
            $table->string('current_tier')->default('bronze'); // bronze, silver, gold, platinum

            $table->timestamps();

            // Foreign key
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            // Indexes
            $table->index('current_tier');
            $table->index('total_earned');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_accounts');
    }
};
