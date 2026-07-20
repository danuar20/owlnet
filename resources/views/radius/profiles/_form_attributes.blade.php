@php
    /** @var string $groupname */
    /** @var array<int, array{attribute:string,op:string,value:string}> $attributes */
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label for="groupname" class="form-label">Profile name (FreeRADIUS group) <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="groupname" name="groupname"
               value="{{ old('groupname', $groupname) }}" required
               placeholder="e.g. profile-20m or voucher">
    </div>
</div>

<hr>

<h6 class="text-muted">Reply attributes</h6>
<div id="attr-rows">
    @foreach ($attributes as $i => $attr)
        <div class="row g-2 mb-2 attr-row">
            <div class="col-5">
                <input type="text" class="form-control" name="attributes[{{ $i }}][attribute]"
                       value="{{ $attr['attribute'] }}" placeholder="Attribute (e.g. Mikrotik-Rate-Limit)">
            </div>
            <div class="col-2">
                <input type="text" class="form-control" name="attributes[{{ $i }}][op]"
                       value="{{ $attr['op'] ?? ':=' }}" maxlength="2" placeholder=":=">
            </div>
            <div class="col-4">
                <input type="text" class="form-control" name="attributes[{{ $i }}][value]"
                       value="{{ $attr['value'] }}" placeholder="Value (e.g. 2048k/4096k)">
            </div>
            <div class="col-1">
                <button type="button" class="btn btn-outline-danger btn-sm remove-row">×</button>
            </div>
        </div>
    @endforeach
</div>

<button type="button" class="btn btn-outline-secondary btn-sm mb-3" id="add-row">+ Add attribute</button>

@push('scripts')
<script>
    (function () {
        let idx = document.querySelectorAll('.attr-row').length;
        const wrap = document.getElementById('attr-rows');
        document.getElementById('add-row').addEventListener('click', function () {
            const row = document.createElement('div');
            row.className = 'row g-2 mb-2 attr-row';
            row.innerHTML = `
                <div class="col-5"><input type="text" class="form-control" name="attributes[${idx}][attribute]" placeholder="Attribute (e.g. Session-Timeout)"></div>
                <div class="col-2"><input type="text" class="form-control" name="attributes[${idx}][op]" value=":=" maxlength="2"></div>
                <div class="col-4"><input type="text" class="form-control" name="attributes[${idx}][value]" placeholder="Value (e.g. 28800)"></div>
                <div class="col-1"><button type="button" class="btn btn-outline-danger btn-sm remove-row">×</button></div>`;
            wrap.appendChild(row);
            idx++;
        });
        wrap.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-row')) {
                e.target.closest('.attr-row').remove();
            }
        });
    })();
</script>
@endpush
