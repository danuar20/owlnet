@php
    /** @var \App\Models\Billing\Subscription $subscription */
    /** @var \Illuminate\Database\Eloquent\Collection $customers */
    /** @var \Illuminate\Database\Eloquent\Collection $packages */
    /** @var \Illuminate\Database\Eloquent\Collection $routers */
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label for="user_id" class="form-label">Customer <span class="text-danger">*</span></label>
        <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id" required>
            <option value="">— select —</option>
            @foreach ($customers as $c)
                <option value="{{ $c->id }}" @selected(old('user_id', $subscription->user_id) === $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
        @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="package_id" class="form-label">Package <span class="text-danger">*</span></label>
        <select class="form-select @error('package_id') is-invalid @enderror" id="package_id" name="package_id" required>
            <option value="">— select —</option>
            @foreach ($packages as $p)
                <option value="{{ $p->id }}"
                        data-price="{{ $p->price }}"
                        data-duration="{{ $p->duration_days }}"
                        @selected(old('package_id', $subscription->package_id) === $p->id)>{{ $p->name }} ({{ $p->duration_days }}d)</option>
            @endforeach
        </select>
        @error('package_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="router_id" class="form-label">Router</label>
        <select class="form-select @error('router_id') is-invalid @enderror" id="router_id" name="router_id">
            <option value="">— none —</option>
            @foreach ($routers as $r)
                <option value="{{ $r->id }}" @selected(old('router_id', $subscription->router_id) === $r->id)>{{ $r->name }}</option>
            @endforeach
        </select>
        @error('router_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="username" class="form-label">RADIUS Username</label>
        <input type="text" class="form-control @error('username') is-invalid @enderror"
               id="username" name="username" value="{{ old('username', $subscription->username) }}"
               placeholder="auto-generated if left empty">
        <small class="text-muted">Left empty → auto-generated (e.g. OWL5B9D43D1).</small>
        @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="started_at" class="form-label">Started At</label>
        <input type="datetime-local" class="form-control @error('started_at') is-invalid @enderror"
               id="started_at" name="started_at" value="{{ old('started_at', $subscription->started_at?->format('Y-m-d\TH:i')) }}">
        @error('started_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="expired_at" class="form-label">Expired At</label>
        <input type="datetime-local" class="form-control @error('expired_at') is-invalid @enderror"
               id="expired_at" name="expired_at" value="{{ old('expired_at', $subscription->expired_at?->format('Y-m-d\TH:i')) }}">
        @error('expired_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="price" class="form-label">Price (Rp)</label>
        <input type="number" step="0.01" min="0" class="form-control @error('price') is-invalid @enderror"
               id="price" name="price" value="{{ old('price', $subscription->price ?? 0) }}">
        @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label for="remarks" class="form-label">Remarks</label>
        <textarea class="form-control" id="remarks" name="remarks" rows="2">{{ old('remarks', $subscription->remarks) }}</textarea>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const pkg = document.getElementById('package_id');
        const price = document.getElementById('price');
        const start = document.getElementById('started_at');
        const end = document.getElementById('expired_at');

        function toLocalInput(date) {
            const pad = (n) => String(n).padStart(2, '0');
            return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate())
                + 'T' + pad(date.getHours()) + ':' + pad(date.getMinutes());
        }

        pkg.addEventListener('change', function () {
            const opt = pkg.options[pkg.selectedIndex];
            const p = parseFloat(opt.getAttribute('data-price') || '');
            const dur = parseInt(opt.getAttribute('data-duration') || '0', 10);

            if (! isNaN(p)) {
                price.value = p;
            }
            if (dur > 0) {
                const now = new Date();
                const exp = new Date(now.getTime() + dur * 24 * 60 * 60 * 1000);
                if (! start.value) {
                    start.value = toLocalInput(now);
                }
                end.value = toLocalInput(exp);
            }
        });
    })();
</script>
@endpush
