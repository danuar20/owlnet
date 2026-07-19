<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Append-only payment event / gateway callback log (billing schema).
     *
     * No soft deletes: this is an immutable audit trail.
     */
    public function up(): void
    {
        Schema::create('billing.payment_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('payment_id');
            $table->uuid('user_id')->nullable();
            $table->string('level')->default('info'); // info|warning|error
            $table->string('event');
            $table->text('message')->nullable();
            $table->jsonb('payload')->nullable();
            $table->timestamps();

            $table->foreign('payment_id')->references('id')->on('billing.payments')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('billing.users')->nullOnDelete();
            $table->index(['level']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing.payment_logs');
    }
};
