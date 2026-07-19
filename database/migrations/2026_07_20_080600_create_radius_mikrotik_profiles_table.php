<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * MikroTik PPP / Hotspot profiles bound to a router (radius schema).
     */
    public function up(): void
    {
        Schema::create('radius.mikrotik_profiles', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('router_id')->nullable();
            $table->string('name');
            $table->string('profile_type')->default('ppp'); // ppp|hotspot
            $table->string('rate_limit')->nullable();        // e.g. "10M/10M"
            $table->unsignedInteger('session_timeout')->nullable(); // seconds
            $table->unsignedInteger('idle_timeout')->nullable();    // seconds
            $table->boolean('is_active')->default(true)->index();
            $table->text('remarks')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('router_id')->references('id')->on('radius.routers')->nullOnDelete();
            $table->unique(['router_id', 'name']);
            $table->index(['profile_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('radius.mikrotik_profiles');
    }
};
