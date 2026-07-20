@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Edit Subscription</h1>
        <a href="{{ route('subscriptions.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('subscriptions.update', $subscription) }}" method="POST">
                @csrf @method('PUT')
                @include('billing.subscriptions._form')
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <a href="{{ route('subscriptions.show', $subscription) }}" class="btn btn-link">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
