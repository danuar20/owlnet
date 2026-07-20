@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header">Add User</div>
                <div class="card-body">
                    @include('users._form', ['user' => new \App\Models\User])
                </div>
            </div>
        </div>
    </div>
@endsection
