<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit trail for subscription status changes (activate / suspend / renew /
 * expire / cancel). Kept in billing schema next to subscriptions.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing.subscription_histories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('subscription_id');
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->string('action'); // activate|suspend|renew|expire|cancel
            $table->text('note')->nullable();
            $table->unsignedBigInteger('changed_by')->nullable(); // staff id (public.users, bigint)
            $table->timestamps();

            $table->foreign('subscription_id')
                ->references('id')->on('billing.subscriptions')
                ->cascadeOnDelete();
            $table->index(['subscription_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing.subscription_histories');
    }
};
