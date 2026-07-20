<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Invoice line items (Invoice Detail). Each row belongs to one invoice
 * (billing.payments) and contributes to the invoice subtotal.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing.invoice_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('invoice_id');
            $table->string('description');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('amount', 12, 2)->default(0); // quantity * unit_price
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('invoice_id')
                ->references('id')->on('billing.payments')
                ->cascadeOnDelete();
            $table->index(['invoice_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing.invoice_items');
    }
};
