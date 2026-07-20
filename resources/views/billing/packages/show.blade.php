@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">{{ $package->name }}</h1>
        <div>
            <a href="{{ route('packages.index') }}" class="btn btn-outline-secondary">Back</a>
            <a href="{{ route('packages.edit', $package) }}" class="btn btn-primary">Edit</a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header">Package Details</div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Status</dt>
                <dd class="col-sm-9">
                    <span class="badge bg-{{ $package->statusColor() }}">{{ $package->statusLabel() }}</span>
                </dd>

                <dt class="col-sm-3">Code</dt>
                <dd class="col-sm-9"><code>{{ $package->code }}</code></dd>

                <dt class="col-sm-3">Duration</dt>
                <dd class="col-sm-9">{{ $package->duration_days }} days</dd>

                <dt class="col-sm-3">Speed (Down / Up)</dt>
                <dd class="col-sm-9">{{ $package->speed_download ?: '—' }} / {{ $package->speed_upload ?: '—' }}</dd>

                <dt class="col-sm-3">Price</dt>
                <dd class="col-sm-9">Rp {{ number_format((float) $package->price, 0, ',', '.') }}</dd>

                <dt class="col-sm-3">Radius Profile</dt>
                <dd class="col-sm-9">{{ $package->radius_profile ?: '—' }}</dd>

                <dt class="col-sm-3">Description</dt>
                <dd class="col-sm-9">{{ $package->description ?: '—' }}</dd>
            </dl>
        </div>
    </div>
@endsection
