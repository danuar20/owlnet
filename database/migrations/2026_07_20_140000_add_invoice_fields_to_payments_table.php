<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extend billing.payments into a full invoice record.
 *
 * Adds: invoice detail totals (subtotal/tax/discount/promo), due date,
 * explicit invoice status, and free-text notes. Guarded with hasColumn so
 * it is a safe, additive NO-OP if already present.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billing.payments', function (Blueprint $table): void {
            if (! Schema::hasColumn('billing.payments', 'subtotal')) {
                $table->decimal('subtotal', 12, 2)->default(0)->after('amount');
            }
            if (! Schema::hasColumn('billing.payments', 'tax_percent')) {
                $table->decimal('tax_percent', 5, 2)->default(0)->after('subtotal');
            }
            if (! Schema::hasColumn('billing.payments', 'tax_amount')) {
                $table->decimal('tax_amount', 12, 2)->default(0)->after('tax_percent');
            }
            if (! Schema::hasColumn('billing.payments', 'discount_amount')) {
                $table->decimal('discount_amount', 12, 2)->default(0)->after('tax_amount');
            }
            if (! Schema::hasColumn('billing.payments', 'promo_code')) {
                $table->string('promo_code')->nullable()->after('discount_amount');
            }
            if (! Schema::hasColumn('billing.payments', 'promo_amount')) {
                $table->decimal('promo_amount', 12, 2)->default(0)->after('promo_code');
            }
            if (! Schema::hasColumn('billing.payments', 'due_date')) {
                $table->timestamp('due_date')->nullable()->after('promo_amount');
            }
            if (! Schema::hasColumn('billing.payments', 'invoice_status')) {
                $table->string('invoice_status')->default('draft')->after('status');
            }
            if (! Schema::hasColumn('billing.payments', 'notes')) {
                $table->text('notes')->nullable()->after('invoice_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('billing.payments', function (Blueprint $table): void {
            foreach (['subtotal', 'tax_percent', 'tax_amount', 'discount_amount', 'promo_code', 'promo_amount', 'due_date', 'invoice_status', 'notes'] as $col) {
                if (Schema::hasColumn('billing.payments', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
