<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add radius_profile to billing.packages (Internet Package Module).
 *
 * Links a package/plan to a FreeRADIUS group name (radgroupreply /
 * radusergroup) so subscribers on this package inherit the group's
 * reply attributes (rate-limit, pool, etc.).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billing.packages', function (Blueprint $table): void {
            if (! Schema::hasColumn('billing.packages', 'radius_profile')) {
                $table->string('radius_profile')->nullable()->after('speed_upload');
            }
        });
    }

    public function down(): void
    {
        Schema::table('billing.packages', function (Blueprint $table): void {
            if (Schema::hasColumn('billing.packages', 'radius_profile')) {
                $table->dropColumn('radius_profile');
            }
        });
    }
};
