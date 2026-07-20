@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">
            Invoice {{ $invoice->invoice_no }}
            <span class="badge bg-{{ $invoice->invoice_status->color() }}">{{ $invoice->invoice_status->label() }}</span>
        </h1>
        <div class="btn-group">
            <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">Back</a>
            <a href="{{ route('invoices.preview', $invoice) }}" target="_blank" class="btn btn-outline-primary">Preview PDF</a>
            <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-primary">Download PDF</a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between">
                    <span>Invoice Details</span>
                    <form action="{{ route('invoices.void', $invoice) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Void this invoice?');">
                        @csrf
                        <button class="btn btn-sm btn-outline-dark">Void</button>
                    </form>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>#</th><th>Description</th><th class="text-end">Qty</th><th class="text-end">Unit</th><th class="text-end">Amount</th></tr></thead>
                        <tbody>
                            @forelse ($invoice->items as $i => $it)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $it->description }}</td>
                                    <td class="text-end">{{ $it->quantity }}</td>
                                    <td class="text-end">Rp {{ number_format((float) $it->unit_price, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format((float) $it->amount, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-muted text-center py-3">No items</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr><td colspan="4" class="text-end">Subtotal</td><td class="text-end">Rp {{ number_format((float) $invoice->subtotal, 0, ',', '.') }}</td></tr>
                            <tr><td colspan="4" class="text-end">Tax ({{ number_format((float) $invoice->tax_percent, 2) }}%)</td><td class="text-end">Rp {{ number_format((float) $invoice->tax_amount, 0, ',', '.') }}</td></tr>
                            <tr><td colspan="4" class="text-end">Discount</td><td class="text-end">Rp {{ number_format((float) $invoice->discount_amount, 0, ',', '.') }}</td></tr>
                            @if ($invoice->promo_amount > 0)
                                <tr><td colspan="4" class="text-end">Promo {{ $invoice->promo_code ? '('.$invoice->promo_code.')' : '' }}</td><td class="text-end">Rp {{ number_format((float) $invoice->promo_amount, 0, ',', '.') }}</td></tr>
                            @endif
                            <tr class="fw-bold"><td colspan="4" class="text-end">Total</td><td class="text-end">Rp {{ number_format((float) $invoice->amount, 0, ',', '.') }}</td></tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header">Info</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">Customer</dt><dd class="col-7">{{ $invoice->user->name ?? '—' }}</dd>
                        <dt class="col-5">Due</dt><dd class="col-7">{{ $invoice->due_date?->format('Y-m-d') ?? '—' }}</dd>
                        <dt class="col-5">Method</dt><dd class="col-7">{{ ucfirst($invoice->method) }}</dd>
                        <dt class="col-5">Paid At</dt><dd class="col-7">{{ $invoice->paid_at?->format('Y-m-d H:i') ?? '—' }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header">Actions</div>
                <div class="card-body d-grid gap-2">
                    @if ($invoice->invoice_status === \App\Enums\InvoiceStatus::DRAFT)
                        <form action="{{ route('invoices.send', $invoice) }}" method="POST">@csrf<button class="btn btn-info">Mark Sent</button></form>
                    @endif
                    @if ($invoice->invoice_status !== \App\Enums\InvoiceStatus::PAID && $invoice->invoice_status !== \App\Enums\InvoiceStatus::VOID)
                        <form action="{{ route('invoices.pay', $invoice) }}" method="POST">@csrf<button class="btn btn-success">Mark Paid</button></form>
                    @endif
                    <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-outline-primary">Edit</a>
                </div>
            </div>
        </div>
    </div>
@endsection
