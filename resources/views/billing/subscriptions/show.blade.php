@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">
            {{ $subscription->user->name ?? 'Subscription' }}
            <span class="badge bg-{{ $subscription->status->color() }}">{{ $subscription->status->label() }}</span>
        </h1>
        <div>
            <a href="{{ route('subscriptions.index') }}" class="btn btn-outline-secondary">Back</a>
            <a href="{{ route('subscriptions.edit', $subscription) }}" class="btn btn-primary">Edit</a>
            @can('super_admin')
                <form action="{{ route('subscriptions.destroy', $subscription) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Delete this subscription permanently? This also removes the RADIUS user.');">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger">Delete</button>
                </form>
            @endcan
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3">
        <div class="col-md-7">
            <div class="card shadow-sm mb-3">
                <div class="card-header">Details</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Customer</dt><dd class="col-sm-8">{{ $subscription->user->name ?? '—' }}</dd>
                        <dt class="col-sm-4">Package</dt><dd class="col-sm-8">{{ $subscription->package->name ?? '—' }}</dd>
                        <dt class="col-sm-4">Router</dt><dd class="col-sm-8">{{ $subscription->router->name ?? '—' }}</dd>
                        <dt class="col-sm-4">RADIUS User</dt><dd class="col-sm-8"><code>{{ $subscription->username ?? '—' }}</code></dd>
                        <dt class="col-sm-4">Started</dt><dd class="col-sm-8">{{ $subscription->started_at?->format('Y-m-d H:i') ?? '—' }}</dd>
                        <dt class="col-sm-4">Expires</dt><dd class="col-sm-8">{{ $subscription->expired_at?->format('Y-m-d H:i') ?? '—' }}</dd>
                        <dt class="col-sm-4">Days Left</dt><dd class="col-sm-8">{{ $subscription->daysRemaining() ?? '—' }}</dd>
                        <dt class="col-sm-4">Price</dt><dd class="col-sm-8">Rp {{ number_format((float) $subscription->price, 0, ',', '.') }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header">History</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>Action</th><th>From</th><th>To</th><th>When</th></tr></thead>
                        <tbody>
                            @forelse ($history as $h)
                                <tr>
                                    <td>{{ $h->action }}</td>
                                    <td>{{ $h->from_status ?? '—' }}</td>
                                    <td>{{ $h->to_status }}</td>
                                    <td>{{ $h->created_at->format('Y-m-d H:i') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-muted text-center py-3">No history yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-header">Lifecycle Actions</div>
                <div class="card-body d-grid gap-2">
                    @if ($subscription->status !== \App\Enums\SubscriptionStatus::ACTIVE)
                        <form action="{{ route('subscriptions.activate', $subscription) }}" method="POST">
                            @csrf <button class="btn btn-success">Activate</button>
                        </form>
                    @endif
                    @if ($subscription->status === \App\Enums\SubscriptionStatus::ACTIVE)
                        <form action="{{ route('subscriptions.suspend', $subscription) }}" method="POST">
                            @csrf <button class="btn btn-warning">Suspend</button>
                        </form>
                        <form action="{{ route('subscriptions.renew', $subscription) }}" method="POST">
                            @csrf <button class="btn btn-info">Renew</button>
                        </form>
                    @endif
                    @if ($subscription->status !== \App\Enums\SubscriptionStatus::EXPIRED)
                        <form action="{{ route('subscriptions.expire', $subscription) }}" method="POST"
                              onsubmit="return confirm('Mark expired?');">
                            @csrf <button class="btn btn-outline-secondary">Mark Expired</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
