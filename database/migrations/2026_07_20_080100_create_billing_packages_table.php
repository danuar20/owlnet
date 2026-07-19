<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Internet packages / plans (billing schema).
     */
    public function up(): void
    {
        Schema::create('billing.packages', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('code')->unique();
            $table->decimal('price', 12, 2)->default(0);
            $table->unsignedInteger('duration_days')->default(30);
            $table->string('speed_download')->nullable(); // e.g. "10M"
            $table->string('speed_upload')->nullable();   // e.g. "10M"
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing.packages');
    }
};
