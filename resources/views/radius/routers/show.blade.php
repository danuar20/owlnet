@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">{{ $router->name }}</h1>
        <div>
            <a href="{{ route('routers.index') }}" class="btn btn-outline-secondary">Back</a>
            <a href="{{ route('routers.edit', $router) }}" class="btn btn-primary">Edit</a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">Details</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-{{ $router->statusColor() }} text-uppercase"
                                  id="status-badge-{{ $router->id }}">{{ $router->status }}</span>
                            @unless ($router->is_active)
                                <span class="badge bg-warning text-dark">disabled</span>
                            @endunless
                        </dd>

                        <dt class="col-sm-4">IP Address</dt>
                        <dd class="col-sm-8"><code>{{ $router->ip_address }}</code></dd>

                        <dt class="col-sm-4">NAS Identifier</dt>
                        <dd class="col-sm-8">{{ $router->nas_identifier ?: '—' }}</dd>

                        <dt class="col-sm-4">Location</dt>
                        <dd class="col-sm-8">{{ $router->location ?: '—' }}</dd>

                        <dt class="col-sm-4">API</dt>
                        <dd class="col-sm-8">{{ ucfirst($router->api_type) }} : {{ $router->api_port ?: 8728 }}</dd>

                        <dt class="col-sm-4">Radius Secret</dt>
                        <dd class="col-sm-8">{{ $router->radius_secret ? '•••••• (set)' : '—' }}</dd>

                        <dt class="col-sm-4">Last Seen</dt>
                        <dd class="col-sm-8">{{ $router->last_seen_at?->diffForHumans() ?? 'never' }}</dd>

                        <dt class="col-sm-4">Remarks</dt>
                        <dd class="col-sm-8">{{ $router->remarks ?: '—' }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header">Connectivity</div>
                <div class="card-body d-grid gap-2">
                    <button type="button" class="btn btn-outline-secondary js-ping"
                            data-url="{{ route('routers.ping', $router) }}">Ping Test</button>
                    <button type="button" class="btn btn-outline-info js-test"
                            data-url="{{ route('routers.test-connection', $router) }}"
                            data-badge="status-badge-{{ $router->id }}">Connection Test</button>
                    <div id="test-result" class="mt-2"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const token = document.querySelector('meta[name="csrf-token"]').content;
        const resultBox = document.getElementById('test-result');

        function showResult(ok, message) {
            resultBox.innerHTML =
                '<div class="alert alert-' + (ok ? 'success' : 'danger') + ' py-2 mb-0">' + message + '</div>';
        }

        async function run(btn) {
            const original = btn.textContent;
            btn.disabled = true; btn.textContent = 'Testing...';
            try {
                const res = await fetch(btn.dataset.url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                });
                const data = await res.json();
                showResult(data.ok, data.message);
                if (btn.dataset.badge && data.status) {
                    const badge = document.getElementById(btn.dataset.badge);
                    if (badge) {
                        badge.textContent = data.status;
                        badge.className = 'badge text-uppercase bg-' +
                            (data.status === 'online' ? 'success' : data.status === 'offline' ? 'danger' : 'secondary');
                    }
                }
            } catch (e) {
                showResult(false, 'Request failed: ' + e.message);
            } finally {
                btn.disabled = false; btn.textContent = original;
            }
        }

        document.querySelectorAll('.js-ping, .js-test').forEach(function (btn) {
            btn.addEventListener('click', () => run(btn));
        });
    });
</script>
@endpush
