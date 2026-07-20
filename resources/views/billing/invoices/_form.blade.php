@php
    /** @var \App\Models\Billing\Payment $invoice */
    /** @var \Illuminate\Database\Eloquent\Collection $customers */
    /** @var \Illuminate\Database\Eloquent\Collection $subscriptions */
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label for="user_id" class="form-label">Customer <span class="text-danger">*</span></label>
        <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id" required>
            <option value="">— select —</option>
            @foreach ($customers as $c)
                <option value="{{ $c->id }}" @selected(old('user_id', $invoice->user_id) === $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
        @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="subscription_id" class="form-label">Subscription</label>
        <select class="form-select" id="subscription_id" name="subscription_id">
            <option value="">— none —</option>
            @foreach ($subscriptions as $s)
                <option value="{{ $s->id }}"
                        data-price="{{ $s->price ?: ($s->package?->price ?: 0) }}"
                        data-label="{{ $s->package?->name ?: 'Subscription' }}"
                        @selected(old('subscription_id', $invoice->subscription_id) === $s->id)>
                    {{ $s->user->name ?? 'Sub' }} ({{ $s->package->name ?? '—' }})
                </option>
            @endforeach
        </select>
        <small class="text-muted">Selecting a subscription auto-fills a line item with its price.</small>
    </div>

    <div class="col-md-4">
        <label for="invoice_no" class="form-label">Invoice #</label>
        <input type="text" class="form-control" id="invoice_no" name="invoice_no"
               value="{{ old('invoice_no', $invoice->invoice_no) }}" placeholder="auto-generated">
    </div>
    <div class="col-md-4">
        <label for="due_date" class="form-label">Due Date</label>
        <input type="date" class="form-control" id="due_date" name="due_date"
               value="{{ old('due_date', $invoice->due_date?->format('Y-m-d')) }}">
    </div>
    <div class="col-md-4">
        <label for="method" class="form-label">Method</label>
        <select class="form-select" id="method" name="method">
            @foreach (['cash','transfer','gateway'] as $m)
                <option value="{{ $m }}" @selected(old('method', $invoice->method ?? 'cash') === $m)>{{ ucfirst($m) }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <label for="tax_percent" class="form-label">Tax %</label>
        <input type="number" step="0.01" min="0" max="100" class="form-control" id="tax_percent"
               name="tax_percent" value="{{ old('tax_percent', $invoice->tax_percent ?? 0) }}">
    </div>
    <div class="col-md-3">
        <label for="discount_amount" class="form-label">Discount (Rp)</label>
        <input type="number" step="0.01" min="0" class="form-control" id="discount_amount"
               name="discount_amount" value="{{ old('discount_amount', $invoice->discount_amount ?? 0) }}">
    </div>
    <div class="col-md-3">
        <label for="promo_code" class="form-label">Promo Code</label>
        <input type="text" class="form-control" id="promo_code" name="promo_code"
               value="{{ old('promo_code', $invoice->promo_code) }}">
    </div>
    <div class="col-md-3">
        <label for="promo_amount" class="form-label">Promo Amount (Rp)</label>
        <input type="number" step="0.01" min="0" class="form-control" id="promo_amount"
               name="promo_amount" value="{{ old('promo_amount', $invoice->promo_amount ?? 0) }}">
    </div>

    <div class="col-12">
        <label for="notes" class="form-label">Notes</label>
        <textarea class="form-control" id="notes" name="notes" rows="2">{{ old('notes', $invoice->notes) }}</textarea>
    </div>

    <hr class="col-12">
    <h6 class="col-12 text-muted">Invoice Details (line items)</h6>
    <div id="item-rows" class="col-12">
        @foreach (old('items', $invoice->items->toArray()) as $i => $it)
            <div class="row g-2 mb-2 item-row">
                <div class="col-5">
                    <input type="text" class="form-control" name="items[{{ $i }}][description]"
                           value="{{ is_array($it) ? $it['description'] : $it['description'] ?? '' }}" placeholder="Description">
                </div>
                <div class="col-2">
                    <input type="number" class="form-control" name="items[{{ $i }}][quantity]" min="1"
                           value="{{ is_array($it) ? ($it['quantity'] ?? 1) : ($it['quantity'] ?? 1) }}" placeholder="Qty">
                </div>
                <div class="col-4">
                    <input type="number" step="0.01" min="0" class="form-control" name="items[{{ $i }}][unit_price]"
                           value="{{ is_array($it) ? $it['unit_price'] : $it['unit_price'] ?? 0 }}" placeholder="Unit price">
                </div>
                <div class="col-1">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-row">×</button>
                </div>
            </div>
        @endforeach
    </div>
    <div class="col-12">
        <button type="button" class="btn btn-outline-secondary btn-sm" id="add-item">+ Add item</button>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const wrap = document.getElementById('item-rows');
        let idx = document.querySelectorAll('.item-row').length;

        function rowHtml(i, desc = '', qty = 1, price = '') {
            return `
                <div class="row g-2 mb-2 item-row">
                    <div class="col-5"><input type="text" class="form-control" name="items[${i}][description]" value="${desc}" placeholder="Description"></div>
                    <div class="col-2"><input type="number" class="form-control" name="items[${i}][quantity]" min="1" value="${qty}"></div>
                    <div class="col-4"><input type="number" step="0.01" min="0" class="form-control unit-price" name="items[${i}][unit_price]" value="${price}" placeholder="Unit price"></div>
                    <div class="col-1"><button type="button" class="btn btn-outline-danger btn-sm remove-row">×</button></div>
                </div>`;
        }

        document.getElementById('add-item').addEventListener('click', function () {
            wrap.insertAdjacentHTML('beforeend', rowHtml(idx));
            idx++;
        });

        wrap.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-row')) {
                e.target.closest('.item-row').remove();
            }
        });

        // Selecting a subscription auto-fills the first line item with its price.
        const subSelect = document.getElementById('subscription_id');
        subSelect.addEventListener('change', function () {
            const opt = subSelect.options[subSelect.selectedIndex];
            const price = opt.getAttribute('data-price') || '';
            const label = opt.getAttribute('data-label') || 'Subscription';
            if (! price) {
                return;
            }
            let first = wrap.querySelector('.item-row');
            if (! first) {
                wrap.insertAdjacentHTML('beforeend', rowHtml(idx, label, 1, price));
                idx++;
            } else {
                first.querySelector('input[name$="[description]"]').value = label;
                first.querySelector('.unit-price').value = price;
                const q = first.querySelector('input[name$="[quantity]"]');
                if (! q.value) {
                    q.value = 1;
                }
            }
        });

        // On create (no existing items), start with one empty row so the
        // price field is visible and always submitted.
        if (idx === 0) {
            wrap.insertAdjacentHTML('beforeend', rowHtml(0));
            idx = 1;
        }
    })();
</script>
@endpush
