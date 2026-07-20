@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Internet Packages</h1>
        <a href="{{ route('packages.create') }}" class="btn btn-primary">Add Package</a>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>All Packages</span>
            <span class="badge bg-secondary">{{ $packages->count() }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Duration</th>
                        <th>Speed (D/U)</th>
                        <th>Price</th>
                        <th>Radius Profile</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($packages as $package)
                        <tr>
                            <td>
                                <a href="{{ route('packages.show', $package) }}" class="fw-semibold text-decoration-none">
                                    {{ $package->name }}
                                </a>
                                <div><small class="text-muted"><code>{{ $package->code }}</code></small></div>
                            </td>
                            <td>{{ $package->duration_days }} days</td>
                            <td>{{ $package->speed_download ?: '—' }} / {{ $package->speed_upload ?: '—' }}</td>
                            <td>Rp {{ number_format((float) $package->price, 0, ',', '.') }}</td>
                            <td>{{ $package->radius_profile ?: '—' }}</td>
                            <td>
                                <span class="badge bg-{{ $package->statusColor() }}">{{ $package->statusLabel() }}</span>
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('packages.edit', $package) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form action="{{ route('packages.destroy', $package) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete package {{ $package->name }}?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                No packages yet. <a href="{{ route('packages.create') }}">Add the first one</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
