<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add a (plaintext) RADIUS password column to billing.subscriptions so the
 * generated login credential can be stored alongside the username.
 * Guarded with hasColumn so it is a safe, additive NO-OP if already present.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billing.subscriptions', function (Blueprint $table): void {
            if (! Schema::hasColumn('billing.subscriptions', 'password')) {
                $table->string('password')->nullable()->after('username');
            }
        });
    }

    public function down(): void
    {
        Schema::table('billing.subscriptions', function (Blueprint $table): void {
            if (Schema::hasColumn('billing.subscriptions', 'password')) {
                $table->dropColumn('password');
            }
        });
    }
};
