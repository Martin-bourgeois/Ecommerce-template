<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->morphs('addressable');
            $table->enum('type', ['shipping', 'billing']);
            $table->string('first_name');
            $table->string('last_name');
            $table->string('company')->nullable();
            $table->string('email');
            $table->string('phone');
            $table->string('street_address');
            $table->string('city');
            $table->string('postal_code');
            $table->string('country', 2);
            $table->string('state_province')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['type', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
