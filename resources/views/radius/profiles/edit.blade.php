@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Edit Radius Profile</h1>
        <a href="{{ route('radius-profiles.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('radius-profiles.update', $groupname) }}" method="POST">
                @csrf @method('PUT')
                @include('radius.profiles._form_attributes')
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="{{ route('radius-profiles.show', $groupname) }}" class="btn btn-link">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
