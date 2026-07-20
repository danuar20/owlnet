<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FreeRADIUS group tables (radgroupreply / radgroupcheck).
 *
 * Guarded with hasTable() so this is a NO-OP on the live radius DB (where
 * FreeRADIUS already created them) and only materialises them in disposable
 * test databases, where the Radius Profile module needs them to run.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('public.radgroupreply')) {
            Schema::create('public.radgroupreply', function (Blueprint $table): void {
                $table->increments('id');
                $table->text('groupname')->default('');
                $table->text('attribute')->default('');
                $table->string('op', 2)->default('=');
                $table->text('value')->default('');
                $table->index('groupname', 'radgroupreply_groupname_idx');
            });
        }

        if (! Schema::hasTable('public.radgroupcheck')) {
            Schema::create('public.radgroupcheck', function (Blueprint $table): void {
                $table->increments('id');
                $table->text('groupname')->default('');
                $table->text('attribute')->default('');
                $table->string('op', 2)->default('==');
                $table->text('value')->default('');
                $table->index('groupname', 'radgroupcheck_groupname_idx');
            });
        }
    }

    public function down(): void
    {
        // Non-destructive: never drop live FreeRADIUS tables.
    }
};
