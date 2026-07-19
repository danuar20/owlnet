<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extend radius.routers with Router Module fields:
 * radius_secret, nas_identifier, location, status.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('radius.routers', function (Blueprint $table): void {
            if (! Schema::hasColumn('radius.routers', 'radius_secret')) {
                $table->string('radius_secret')->nullable()->after('password');
            }
            if (! Schema::hasColumn('radius.routers', 'nas_identifier')) {
                $table->string('nas_identifier')->nullable()->after('radius_secret');
            }
            if (! Schema::hasColumn('radius.routers', 'location')) {
                $table->string('location')->nullable()->after('nas_identifier');
            }
            if (! Schema::hasColumn('radius.routers', 'status')) {
                $table->string('status')->default('inactive')->index()->after('is_active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('radius.routers', function (Blueprint $table): void {
            foreach (['radius_secret', 'nas_identifier', 'location', 'status'] as $column) {
                if (Schema::hasColumn('radius.routers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
