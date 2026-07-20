@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Invoices</h1>
        <a href="{{ route('invoices.create') }}" class="btn btn-primary">New Invoice</a>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header">All Invoices</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Invoice #</th><th>Customer</th><th>Status</th>
                        <th class="text-end">Total</th><th>Due</th><th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $inv)
                        <tr>
                            <td><a href="{{ route('invoices.show', $inv) }}">{{ $inv->invoice_no }}</a></td>
                            <td>{{ $inv->user->name ?? '—' }}</td>
                            <td>
                                <span class="badge bg-{{ $inv->invoice_status->color() }}">{{ $inv->invoice_status->label() }}</span>
                            </td>
                            <td class="text-end">Rp {{ number_format((float) $inv->amount, 0, ',', '.') }}</td>
                            <td>{{ $inv->due_date?->format('Y-m-d') ?? '—' }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('invoices.preview', $inv) }}" target="_blank" class="btn btn-sm btn-outline-secondary">PDF</a>
                                <a href="{{ route('invoices.show', $inv) }}" class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No invoices yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
