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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('status');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('email')->nullable()->after('last_name');
            $table->string('phone')->nullable()->after('email');
            $table->string('street')->nullable()->after('phone');
            $table->string('city')->nullable()->after('street');
            $table->string('postal_code')->nullable()->after('city');
            $table->string('country')->nullable()->after('postal_code');
            $table->string('shipping_method')->default('standard')->after('country');
            $table->string('payment_method')->default('card')->after('shipping_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name', 'email', 'phone', 'street', 'city', 'postal_code', 'country', 'shipping_method', 'payment_method']);
        });
    }
};
