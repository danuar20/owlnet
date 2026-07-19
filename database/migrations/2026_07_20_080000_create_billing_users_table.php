<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Customers / subscribers (billing schema).
     *
     * Distinct from public.users (application staff created by the auth module).
     */
    public function up(): void
    {
        Schema::create('billing.users', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->string('phone')->nullable()->index();
            $table->text('address')->nullable();
            $table->string('status')->default('active')->index(); // active|suspended|inactive
            $table->text('remarks')->nullable();
            $table->uuid('created_by')->nullable(); // public.users (staff) who created the record
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing.users');
    }
};
