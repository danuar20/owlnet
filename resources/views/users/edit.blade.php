@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header">Edit User: {{ $user->name }}</div>
                <div class="card-body">
                    @include('users._form', ['user' => $user])
                </div>
            </div>
        </div>
    </div>
@endsection
