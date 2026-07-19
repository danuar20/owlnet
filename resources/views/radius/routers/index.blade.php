@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Routers</h1>
        <a href="{{ route('routers.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Add Router
        </a>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>All Routers</span>
            <span class="badge bg-secondary">{{ $routers->count() }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>IP Address</th>
                        <th>NAS Identifier</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($routers as $router)
                        <tr>
                            <td>
                                <a href="{{ route('routers.show', $router) }}" class="fw-semibold text-decoration-none">
                                    {{ $router->name }}
                                </a>
                            </td>
                            <td><code>{{ $router->ip_address }}</code></td>
                            <td>{{ $router->nas_identifier ?: '—' }}</td>
                            <td>{{ $router->location ?: '—' }}</td>
                            <td>
                                <span class="badge bg-{{ $router->statusColor() }} text-uppercase"
                                      id="status-badge-{{ $router->id }}">
                                    {{ $router->status }}
                                </span>
                            </td>
                            <td class="text-end text-nowrap">
                                <button type="button"
                                        class="btn btn-sm btn-outline-secondary js-ping"
                                        data-url="{{ route('routers.ping', $router) }}">
                                    Ping
                                </button>
                                <button type="button"
                                        class="btn btn-sm btn-outline-info js-test"
                                        data-url="{{ route('routers.test-connection', $router) }}"
                                        data-badge="status-badge-{{ $router->id }}">
                                    Test
                                </button>
                                <a href="{{ route('routers.edit', $router) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form action="{{ route('routers.destroy', $router) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete router {{ $router->name }}?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                No routers yet. <a href="{{ route('routers.create') }}">Add the first one</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div id="test-result" class="mt-3"></div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const token = document.querySelector('meta[name="csrf-token"]').content;
        const resultBox = document.getElementById('test-result');

        function showResult(ok, message) {
            resultBox.innerHTML =
                '<div class="alert alert-' + (ok ? 'success' : 'danger') +
                ' alert-dismissible fade show">' + message +
                '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        }

        async function run(btn) {
            const original = btn.textContent;
            btn.disabled = true;
            btn.textContent = '...';
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
                btn.disabled = false;
                btn.textContent = original;
            }
        }

        document.querySelectorAll('.js-ping, .js-test').forEach(function (btn) {
            btn.addEventListener('click', () => run(btn));
        });
    });
</script>
@endpush
