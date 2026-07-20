<?php

declare(strict_types=1);

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\InvoiceRequest;
use App\Models\Billing\Payment;
use App\Models\Billing\Subscription;
use App\Models\Billing\User as BillingUser;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Invoice (billing.payments) CRUD + lifecycle (send / pay / void) + PDF.
 */
class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoices
    ) {}

    public function index(): View
    {
        return view('billing.invoices.index', [
            'invoices' => $this->invoices->list(),
        ]);
    }

    public function create(): View
    {
        return view('billing.invoices.create', [
            'invoice' => new Payment,
            'customers' => BillingUser::orderBy('name')->get(),
            'subscriptions' => Subscription::orderBy('created_at')->get(),
        ]);
    }

    public function store(InvoiceRequest $request): RedirectResponse
    {
        $invoice = $this->invoices->create($request->validated(), $request->items());

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('status', "Invoice {$invoice->invoice_no} created.");
    }

    public function show(string $invoice): View
    {
        $model = $this->invoices->findOrFail($invoice);

        return view('billing.invoices.show', ['invoice' => $model]);
    }

    public function edit(string $invoice): View
    {
        $model = $this->invoices->findOrFail($invoice);

        return view('billing.invoices.edit', [
            'invoice' => $model,
            'customers' => BillingUser::orderBy('name')->get(),
            'subscriptions' => Subscription::orderBy('created_at')->get(),
        ]);
    }

    public function update(InvoiceRequest $request, string $invoice): RedirectResponse
    {
        $updated = $this->invoices->update($invoice, $request->validated(), $request->items());

        return redirect()
            ->route('invoices.show', $updated)
            ->with('status', "Invoice {$updated->invoice_no} updated.");
    }

    public function destroy(string $invoice): RedirectResponse
    {
        $this->invoices->findOrFail($invoice)->delete();

        return redirect()
            ->route('invoices.index')
            ->with('status', 'Invoice deleted.');
    }

    public function send(string $invoice): RedirectResponse
    {
        $this->invoices->markSent($this->invoices->findOrFail($invoice));

        return back()->with('status', 'Invoice marked sent.');
    }

    public function pay(string $invoice): RedirectResponse
    {
        $this->invoices->markPaid($this->invoices->findOrFail($invoice));

        return back()->with('status', 'Invoice marked paid.');
    }

    public function void(string $invoice): RedirectResponse
    {
        $this->invoices->void($this->invoices->findOrFail($invoice));

        return back()->with('status', 'Invoice voided.');
    }

    public function download(string $invoice)
    {
        $model = $this->invoices->findOrFail($invoice);

        return $this->invoices->pdf($model)->download("{$model->invoice_no}.pdf");
    }

    public function stream(string $invoice)
    {
        $model = $this->invoices->findOrFail($invoice);

        return $this->invoices->pdf($model)->stream("{$model->invoice_no}.pdf");
    }
}
