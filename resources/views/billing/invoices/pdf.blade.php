<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_no }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 13px; color: #1a1a1a; }
        .head { display: flex; justify-content: space-between; margin-bottom: 24px; }
        .brand { font-size: 18px; font-weight: bold; }
        .meta { text-align: right; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border-bottom: 1px solid #ddd; padding: 7px 6px; text-align: left; }
        th { background: #f5f5f5; }
        .num { text-align: right; }
        .totals { margin-top: 16px; width: 280px; margin-left: auto; }
        .totals td { border: none; padding: 4px 6px; }
        .grand { font-weight: bold; font-size: 15px; }
        .badge { padding: 2px 8px; border-radius: 4px; background: #eee; }
        .notes { margin-top: 20px; color: #555; }
        .foot { margin-top: 30px; font-size: 11px; color: #888; text-align: center; }
    </style>
</head>
<body>
    <div class="head">
        <div>
            <div class="brand">OWL NET</div>
            <div>Internet Service Provider</div>
            <div>Jl. Contoh No. 123</div>
            <div>admin@owl.net</div>
        </div>
        <div class="meta">
            <div><strong>Invoice</strong> {{ $invoice->invoice_no }}</div>
            <div>Date: {{ $invoice->created_at->format('Y-m-d') }}</div>
            <div>Due: {{ $invoice->due_date?->format('Y-m-d') ?? '—' }}</div>
            <div>Status:
                <span class="badge">{{ $invoice->invoice_status->label() }}</span>
            </div>
        </div>
    </div>

    <div>
        <strong>Bill To:</strong><br>
        {{ $invoice->user->name ?? '—' }}<br>
        {{ $invoice->user->email ?? '' }}<br>
        {{ $invoice->user->phone ?? '' }}
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Description</th>
                <th class="num">Qty</th>
                <th class="num">Unit Price</th>
                <th class="num">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($invoice->items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->description }}</td>
                    <td class="num">{{ $item->quantity }}</td>
                    <td class="num">Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}</td>
                    <td class="num">Rp {{ number_format((float) $item->amount, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="num">No line items</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td class="num">Rp {{ number_format((float) $invoice->subtotal, 0, ',', '.') }}</td></tr>
        <tr><td>Tax ({{ number_format((float) $invoice->tax_percent, 2) }}%)</td><td class="num">Rp {{ number_format((float) $invoice->tax_amount, 0, ',', '.') }}</td></tr>
        <tr><td>Discount</td><td class="num">Rp {{ number_format((float) $invoice->discount_amount, 0, ',', '.') }}</td></tr>
        @if ($invoice->promo_amount > 0 || $invoice->promo_code)
            <tr><td>Promo {{ $invoice->promo_code ? '('.$invoice->promo_code.')' : '' }}</td><td class="num">Rp {{ number_format((float) $invoice->promo_amount, 0, ',', '.') }}</td></tr>
        @endif
        <tr class="grand"><td>Total</td><td class="num">Rp {{ number_format((float) $invoice->amount, 0, ',', '.') }}</td></tr>
    </table>

    @if ($invoice->notes)
        <div class="notes">{{ $invoice->notes }}</div>
    @endif

    <div class="foot">Thank you for your business — OWL NET</div>
</body>
</html>
