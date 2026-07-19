<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Customer subscriptions to packages (billing schema).
     *
     * References billing.users, billing.packages and (optionally) radius.routers.
     */
    public function up(): void
    {
        Schema::create('billing.subscriptions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('package_id');
            $table->uuid('router_id')->nullable();
            $table->string('username')->nullable()->unique(); // PPPoE / Hotspot login
            $table->string('status')->default('pending')->index(); // pending|active|suspended|expired
            $table->timestamp('started_at')->nullable();
            $table->timestamp('expired_at')->nullable()->index();
            $table->decimal('price', 12, 2)->default(0); // price snapshot at subscription time
            $table->text('remarks')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('billing.users')->cascadeOnDelete();
            $table->foreign('package_id')->references('id')->on('billing.packages')->cascadeOnDelete();
            $table->foreign('router_id')->references('id')->on('radius.routers')->nullOnDelete();
            $table->index(['user_id', 'status']);
            $table->index(['package_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing.subscriptions');
    }
};
