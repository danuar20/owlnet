<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Immutable activity / audit log (radius schema).
     *
     * No soft deletes: append-only audit trail.
     */
    public function up(): void
    {
        Schema::create('radius.activity_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('admin_id')->nullable();
            $table->string('subject_type')->nullable(); // polymorphic: billing.users, radius.routers, ...
            $table->uuid('subject_id')->nullable();
            $table->string('event');
            $table->text('description')->nullable();
            $table->jsonb('payload')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('admin_id')->references('id')->on('radius.admins')->nullOnDelete();
            $table->index(['subject_type', 'subject_id']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('radius.activity_logs');
    }
};
