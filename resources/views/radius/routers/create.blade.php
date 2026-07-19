@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Add Router</h1>
        <a href="{{ route('routers.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('routers.store') }}" method="POST">
                @csrf
                @include('radius.routers._form')
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Create Router</button>
                    <a href="{{ route('routers.index') }}" class="btn btn-link">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
