@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">New Invoice</h1>
        <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('invoices.store') }}" method="POST">
                @csrf
                @include('billing.invoices._form')
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Create Invoice</button>
                    <a href="{{ route('invoices.index') }}" class="btn btn-link">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
