<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('phone')->nullable();
            $table->string('group')->default('standard'); // standard, vip
            $table->decimal('loyalty_points', 10, 2)->default(0);
            $table->date('birth_date')->nullable();
            $table->string('newsletter_status')->default('unsubscribed'); // subscribed, unsubscribed
            $table->timestamps();

            $table->index('group');
            $table->index('newsletter_status');
        });

        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type')->default('billing'); // billing, shipping
            $table->string('first_name');
            $table->string('last_name');
            $table->string('street_address');
            $table->string('street_address_2')->nullable();
            $table->string('city');
            $table->string('state');
            $table->string('postal_code');
            $table->string('country');
            $table->string('phone')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
        Schema::dropIfExists('customer_profiles');
    }
};
