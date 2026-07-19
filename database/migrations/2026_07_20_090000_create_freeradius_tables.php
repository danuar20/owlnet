<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FreeRADIUS SQL schema (public schema).
 *
 * These tables are normally created by the FreeRADIUS PostgreSQL bootstrap
 * (schema.sql) and already exist on the live `radius` database. Each create is
 * therefore guarded by a hasTable() check so this migration is a NO-OP in
 * production and only materialises the tables in disposable test databases,
 * where the RADIUS models and repository need something to run against.
 *
 * Column definitions mirror the canonical FreeRADIUS 3.x PostgreSQL schema.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('public.radcheck')) {
            Schema::create('public.radcheck', function (Blueprint $table): void {
                $table->increments('id');
                $table->text('username')->default('');
                $table->text('attribute')->default('');
                $table->string('op', 2)->default('==');
                $table->text('value')->default('');
                $table->index('username', 'radcheck_username_idx');
            });
        }

        if (! Schema::hasTable('public.radreply')) {
            Schema::create('public.radreply', function (Blueprint $table): void {
                $table->increments('id');
                $table->text('username')->default('');
                $table->text('attribute')->default('');
                $table->string('op', 2)->default('=');
                $table->text('value')->default('');
                $table->index('username', 'radreply_username_idx');
            });
        }

        if (! Schema::hasTable('public.radusergroup')) {
            Schema::create('public.radusergroup', function (Blueprint $table): void {
                $table->increments('id');
                $table->text('username')->default('');
                $table->text('groupname')->default('');
                $table->integer('priority')->default(0);
                $table->index('username', 'radusergroup_username_idx');
            });
        }

        if (! Schema::hasTable('public.radacct')) {
            Schema::create('public.radacct', function (Blueprint $table): void {
                $table->bigIncrements('radacctid');
                $table->text('acctsessionid');
                $table->text('acctuniqueid')->unique('radacct_acctuniqueid_idx');
                $table->text('username')->nullable();
                $table->text('realm')->nullable();
                $table->ipAddress('nasipaddress');
                $table->text('nasportid')->nullable();
                $table->text('nasporttype')->nullable();
                $table->timestampTz('acctstarttime')->nullable();
                $table->timestampTz('acctupdatetime')->nullable();
                $table->timestampTz('acctstoptime')->nullable();
                $table->bigInteger('acctinterval')->nullable();
                $table->bigInteger('acctsessiontime')->nullable();
                $table->text('acctauthentic')->nullable();
                $table->text('connectinfo_start')->nullable();
                $table->text('connectinfo_stop')->nullable();
                $table->bigInteger('acctinputoctets')->nullable();
                $table->bigInteger('acctoutputoctets')->nullable();
                $table->text('calledstationid')->nullable();
                $table->text('callingstationid')->nullable();
                $table->text('acctterminatecause')->nullable();
                $table->text('servicetype')->nullable();
                $table->text('framedprotocol')->nullable();
                $table->ipAddress('framedipaddress')->nullable();
                $table->index('username', 'radacct_username_idx');
                $table->index('acctstoptime', 'radacct_acctstoptime_idx');
            });
        }

        if (! Schema::hasTable('public.radpostauth')) {
            Schema::create('public.radpostauth', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->text('username');
                $table->text('pass')->nullable();
                $table->text('reply')->nullable();
                $table->text('calledstationid')->nullable();
                $table->text('callingstationid')->nullable();
                $table->timestampTz('authdate')->useCurrent();
                $table->text('class')->nullable();
                $table->index('username', 'radpostauth_username_idx');
            });
        }
    }

    public function down(): void
    {
        // Intentionally NON-destructive: never drop live FreeRADIUS tables.
        // Disposable test databases are torn down wholesale, not migrated down.
    }
};
