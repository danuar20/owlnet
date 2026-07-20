<?php

declare(strict_types=1);

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\Billing\InvoiceItem;
use App\Models\Billing\Payment;
use App\Models\Billing\User;
use App\Services\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->service = app(InvoiceService::class);
});

it('creates an invoice with line items and computes totals', function (): void {
    $user = User::factory()->create();
    $items = [
        ['description' => 'Monthly fee', 'quantity' => 1, 'unit_price' => 200000],
        ['description' => 'Router rental', 'quantity' => 1, 'unit_price' => 50000],
    ];

    $invoice = $this->service->create([
        'user_id' => $user->id,
        'tax_percent' => 10,
        'discount_amount' => 10000,
    ], $items);

    expect($invoice->items)->toHaveCount(2)
        ->and($invoice->subtotal)->toBe('250000.00')
        ->and($invoice->tax_amount)->toBe('25000.00')
        ->and($invoice->amount)->toBe('265000.00') // 250000 + 25000 - 10000
        ->and($invoice->invoice_status)->toBe(InvoiceStatus::DRAFT);
});

it('applies promo code and amount', function (): void {
    $user = User::factory()->create();
    $invoice = $this->service->create([
        'user_id' => $user->id,
        'promo_code' => 'OWL10',
        'promo_amount' => 20000,
    ], [
        ['description' => 'Fee', 'quantity' => 1, 'unit_price' => 100000],
    ]);

    expect($invoice->subtotal)->toBe('100000.00')
        ->and($invoice->promo_amount)->toBe('20000.00')
        ->and($invoice->amount)->toBe('80000.00');
});

it('marks an invoice paid', function (): void {
    $invoice = Payment::factory()->create();

    $this->service->markPaid($invoice);

    expect($invoice->fresh()->invoice_status)->toBe(InvoiceStatus::PAID)
        ->and($invoice->fresh()->status)->toBe(PaymentStatus::PAID)
        ->and($invoice->fresh()->paid_at)->not->toBeNull();
});

it('voids an invoice', function (): void {
    $invoice = Payment::factory()->create();

    $this->service->void($invoice);

    expect($invoice->fresh()->invoice_status)->toBe(InvoiceStatus::VOID);
});

it('generates a unique invoice number', function (): void {
    $a = $this->service->generateInvoiceNo();
    $b = $this->service->generateInvoiceNo();

    expect($a)->toStartWith('INV-')
        ->and($a)->not->toBe($b);
});

it('recomputes totals after item changes', function (): void {
    $invoice = Payment::factory()->create();
    InvoiceItem::factory()->count(2)->create(['invoice_id' => $invoice->id]);

    expect($invoice->refresh()->subtotal)->toBeGreaterThan('0.00');
});
