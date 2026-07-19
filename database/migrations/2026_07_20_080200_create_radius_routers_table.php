<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * MikroTik / FreeRADIUS routers (radius schema).
     */
    public function up(): void
    {
        Schema::create('radius.routers', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('ip_address');
            $table->unsignedInteger('api_port')->nullable();
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->string('api_type')->default('mikrotik'); // mikrotik|freeradius
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('last_seen_at')->nullable();
            $table->text('remarks')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['ip_address']);
            $table->unique(['name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('radius.routers');
    }
};
