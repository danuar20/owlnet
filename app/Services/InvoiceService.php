<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\Billing\InvoiceItem;
use App\Models\Billing\Payment;
use App\Repositories\Billing\PaymentRepository;
use Barryvdh\DomPDF\PDF;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Billing / Invoice management: create invoices with line items, compute
 * totals (subtotal + tax - discount - promo), mark paid, and render PDFs.
 */
class InvoiceService
{
    public function __construct(
        private readonly PaymentRepository $payments
    ) {}

    /**
     * @return Collection<int, Payment>
     */
    public function list(): Collection
    {
        return $this->payments->all();
    }

    public function find(string $id): ?Payment
    {
        return $this->payments->find($id);
    }

    public function findOrFail(string $id): Payment
    {
        return $this->payments->findOrFail($id);
    }

    /**
     * Create an invoice with optional line items.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, array{description:string,quantity?:int,unit_price:float|string}>  $items
     */
    public function create(array $data, array $items = []): Payment
    {
        return DB::transaction(function () use ($data, $items): Payment {
            $invoice = $this->payments->create([
                'user_id' => $data['user_id'],
                'subscription_id' => $data['subscription_id'] ?? null,
                'invoice_no' => $data['invoice_no'] ?? $this->generateInvoiceNo(),
                'method' => $data['method'] ?? 'cash',
                'gateway' => $data['gateway'] ?? null,
                'tax_percent' => $data['tax_percent'] ?? 0,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'promo_code' => $data['promo_code'] ?? null,
                'promo_amount' => $data['promo_amount'] ?? 0,
                'due_date' => $data['due_date'] ?? null,
                'notes' => $data['notes'] ?? null,
                'invoice_status' => $data['invoice_status'] ?? InvoiceStatus::DRAFT,
                'status' => $data['status'] ?? PaymentStatus::PENDING,
            ]);

            $this->syncItems($invoice, $items);

            return $invoice->refresh()->recalculate()->save() ? $invoice : $invoice;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, array{description:string,quantity?:int,unit_price:float|string}>  $items
     */
    public function update(string $id, array $data, array $items = []): Payment
    {
        return DB::transaction(function () use ($id, $data, $items): Payment {
            $invoice = $this->payments->findOrFail($id);
            $invoice->fill([
                'subscription_id' => $data['subscription_id'] ?? $invoice->subscription_id,
                'method' => $data['method'] ?? $invoice->method,
                'gateway' => $data['gateway'] ?? $invoice->gateway,
                'tax_percent' => $data['tax_percent'] ?? $invoice->tax_percent,
                'discount_amount' => $data['discount_amount'] ?? $invoice->discount_amount,
                'promo_code' => $data['promo_code'] ?? null,
                'promo_amount' => $data['promo_amount'] ?? $invoice->promo_amount,
                'due_date' => $data['due_date'] ?? $invoice->due_date,
                'notes' => $data['notes'] ?? $invoice->notes,
                'invoice_status' => $data['invoice_status'] ?? $invoice->invoice_status,
            ]);
            $invoice->save();

            $this->syncItems($invoice, $items);

            return $invoice->refresh()->recalculate()->save() ? $invoice : $invoice;
        });
    }

    /**
     * Replace the invoice's line items with the given set.
     *
     * @param  array<int, array{description:string,quantity?:int,unit_price:float|string}>  $items
     */
    private function syncItems(Payment $invoice, array $items): void
    {
        $invoice->items()->delete();

        foreach ($items as $index => $item) {
            $qty = (int) ($item['quantity'] ?? 1);
            $unit = (float) $item['unit_price'];
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => $item['description'],
                'quantity' => $qty,
                'unit_price' => $unit,
                'amount' => $qty * $unit,
                'sort_order' => $index,
            ]);
        }
    }

    /**
     * Mark an invoice paid. Sets both invoice_status and payment status.
     */
    public function markPaid(Payment $invoice, ?string $method = null): Payment
    {
        $invoice->invoice_status = InvoiceStatus::PAID;
        $invoice->status = PaymentStatus::PAID;
        $invoice->paid_at = now();
        if ($method !== null) {
            $invoice->method = $method;
        }
        $invoice->save();

        return $invoice;
    }

    /**
     * Mark an invoice sent (issued to the customer).
     */
    public function markSent(Payment $invoice): Payment
    {
        if ($invoice->invoice_status === InvoiceStatus::DRAFT) {
            $invoice->invoice_status = InvoiceStatus::SENT;
            $invoice->save();
        }

        return $invoice;
    }

    public function void(Payment $invoice): Payment
    {
        $invoice->invoice_status = InvoiceStatus::VOID;
        $invoice->save();

        return $invoice;
    }

    /**
     * Generate a unique, human-friendly invoice number.
     */
    public function generateInvoiceNo(): string
    {
        $prefix = 'INV-'.now()->format('Ym').'-';
        do {
            $no = $prefix.strtoupper(Str::random(5));
        } while (Payment::where('invoice_no', $no)->exists());

        return $no;
    }

    /**
     * Render the invoice as a downloadable PDF (dompdf via the PDF facade).
     */
    public function pdf(Payment $invoice): PDF
    {
        /** @var PDF $pdf */
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('billing.invoices.pdf', ['invoice' => $invoice]);

        return $pdf;
    }
}
