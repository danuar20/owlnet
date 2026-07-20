@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Subscriptions</h1>
        <a href="{{ route('subscriptions.create') }}" class="btn btn-primary">Add Subscription</a>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header">All Subscriptions</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Customer</th><th>Package</th><th>Username</th>
                        <th>Status</th><th>Expires</th><th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($subscriptions as $sub)
                        <tr>
                            <td>{{ $sub->user->name ?? '—' }}</td>
                            <td>{{ $sub->package->name ?? '—' }}</td>
                            <td><code>{{ $sub->username ?? '—' }}</code></td>
                            <td>
                                <span class="badge bg-{{ $sub->status->color() }}">{{ $sub->status->label() }}</span>
                            </td>
                            <td>{{ $sub->expired_at ? $sub->expired_at->format('Y-m-d') : '—' }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('subscriptions.show', $sub) }}" class="btn btn-sm btn-outline-secondary">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No subscriptions yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
