<?php

declare(strict_types=1);

use App\Enums\InvoiceStatus;
use App\Models\Billing\InvoiceItem;
use App\Models\Billing\Payment;
use App\Models\Billing\User;
use App\Models\User as StaffUser;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function staff(): StaffUser
{
    return StaffUser::factory()->create(['role' => 'admin']);
}

it('lists invoices', function (): void {
    Payment::factory()->count(3)->create();

    $this->actingAs(staff())
        ->get(route('invoices.index'))
        ->assertOk()
        ->assertSee('Invoices');
});

it('shows the create form', function (): void {
    $this->actingAs(staff())
        ->get(route('invoices.create'))
        ->assertOk()
        ->assertSee('New Invoice');
});

it('creates an invoice with items', function (): void {
    $user = User::factory()->create();

    $this->actingAs(staff())
        ->post(route('invoices.store'), [
            'user_id' => $user->id,
            'invoice_no' => 'INV-TEST-1',
            'tax_percent' => 10,
            'discount_amount' => 0,
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'items' => [
                ['description' => 'Monthly', 'quantity' => 1, 'unit_price' => 200000],
            ],
        ])
        ->assertRedirect(route('invoices.show', Payment::where('invoice_no', 'INV-TEST-1')->firstOrFail()));

    $inv = Payment::where('invoice_no', 'INV-TEST-1')->firstOrFail();
    expect($inv->items)->toHaveCount(1)
        ->and($inv->subtotal)->toBe('200000.00')
        ->and($inv->amount)->toBe('220000.00'); // +10% tax
});

it('validates required user_id on create', function (): void {
    $this->actingAs(staff())
        ->post(route('invoices.store'), [
            'items' => [['description' => 'x', 'unit_price' => 1000]],
        ])
        ->assertSessionHasErrors('user_id');
});

it('shows an invoice', function (): void {
    $inv = Payment::factory()->create();
    InvoiceItem::factory()->count(2)->create(['invoice_id' => $inv->id]);

    $this->actingAs(staff())
        ->get(route('invoices.show', $inv))
        ->assertOk()
        ->assertSee($inv->invoice_no)
        ->assertSee('Invoice Details');
});

it('updates an invoice', function (): void {
    $inv = Payment::factory()->create();
    $user = User::factory()->create();

    $this->actingAs(staff())
        ->put(route('invoices.update', $inv), [
            'user_id' => $user->id,
            'discount_amount' => 5000,
            'items' => [['description' => 'Updated', 'quantity' => 1, 'unit_price' => 100000]],
        ])
        ->assertRedirect(route('invoices.show', $inv));

    expect($inv->fresh()->discount_amount)->toBe('5000.00')
        ->and($inv->fresh()->items)->toHaveCount(1);
});

it('marks an invoice paid via endpoint', function (): void {
    $inv = Payment::factory()->create();

    $this->actingAs(staff())
        ->post(route('invoices.pay', $inv))
        ->assertRedirect();

    expect($inv->fresh()->invoice_status)->toBe(InvoiceStatus::PAID);
});

it('voids an invoice via endpoint', function (): void {
    $inv = Payment::factory()->create();

    $this->actingAs(staff())
        ->post(route('invoices.void', $inv))
        ->assertRedirect();

    expect($inv->fresh()->invoice_status)->toBe(InvoiceStatus::VOID);
});

it('downloads a PDF invoice', function (): void {
    $inv = Payment::factory()->create();
    InvoiceItem::factory()->count(1)->create(['invoice_id' => $inv->id]);

    $this->actingAs(staff())
        ->get(route('invoices.pdf', $inv))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

it('deletes an invoice', function (): void {
    $inv = Payment::factory()->create();

    $this->actingAs(staff())
        ->delete(route('invoices.destroy', $inv))
        ->assertRedirect(route('invoices.index'));

    expect(Payment::find($inv->id))->toBeNull();
});
