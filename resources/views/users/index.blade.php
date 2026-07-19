@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Users</span>
                        <span class="badge bg-secondary">{{ $users->count() }}</span>
                    </div>
                    <div class="card-body">
                        @forelse ($users as $user)
                            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                <div>
                                    <div>{{ $user->name }}</div>
                                    <small class="text-muted">{{ $user->email }}</small>
                                </div>
                                <span class="badge bg-info text-uppercase">
                                    {{ $user->role->label() }}
                                </span>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No users yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
