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
            $table->string('paypal_proof_path')->nullable()->after('payment_method')->comment('Path to PayPal payment proof file');
            $table->text('paypal_transaction_id')->nullable()->after('paypal_proof_path')->comment('PayPal transaction ID');
            $table->timestamp('payment_verified_at')->nullable()->after('paypal_transaction_id')->comment('When payment was verified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['paypal_proof_path', 'paypal_transaction_id', 'payment_verified_at']);
        });
    }
};
