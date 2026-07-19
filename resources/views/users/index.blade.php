@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header">Users</div>
                    <div class="card-body">
                        @forelse ($users as $user)
                            <div class="d-flex justify-content-between border-bottom py-2">
                                <span>{{ $user->name }}</span>
                                <span class="text-muted">{{ $user->email }}</span>
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
