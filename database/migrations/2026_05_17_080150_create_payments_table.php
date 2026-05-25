<?php

use App\Domains\Order\Enums\PaymentMethod;
use App\Domains\Order\Enums\PaymentStatus;
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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->morphs('payable');
            $table->string('method')->default(PaymentMethod::PAYPAL->value);
            $table->string('status')->default(PaymentStatus::PENDING->value);
            $table->unsignedBigInteger('amount_cents');
            $table->string('currency')->default('EUR');
            $table->string('reference')->unique();
            $table->json('metadata')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index('payable_id');
            $table->index('payable_type');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
