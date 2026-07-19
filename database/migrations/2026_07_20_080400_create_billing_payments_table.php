<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Payments for subscriptions (billing schema).
     */
    public function up(): void
    {
        Schema::create('billing.payments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('subscription_id')->nullable();
            $table->string('invoice_no')->nullable()->unique();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('method')->default('cash'); // cash|transfer|gateway
            $table->string('gateway')->nullable();        // midtrans|xendit|...
            $table->string('status')->default('pending')->index(); // pending|paid|failed|refunded
            $table->timestamp('paid_at')->nullable();
            $table->text('remarks')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('billing.users')->cascadeOnDelete();
            $table->foreign('subscription_id')->references('id')->on('billing.subscriptions')->nullOnDelete();
            $table->index(['method']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing.payments');
    }
};
