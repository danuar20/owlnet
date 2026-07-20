@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0"><code>{{ $groupname }}</code></h1>
        <div>
            <a href="{{ route('radius-profiles.index') }}" class="btn btn-outline-secondary">Back</a>
            <a href="{{ route('radius-profiles.edit', $groupname) }}" class="btn btn-primary">Edit</a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header">Reply attributes (radgroupreply)</div>
        <div class="card-body">
            @if ($attributes)
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Attribute</th><th>Op</th><th>Value</th></tr></thead>
                    <tbody>
                        @foreach ($attributes as $a)
                            <tr>
                                <td><code>{{ $a->attribute }}</code></td>
                                <td>{{ $a->op }}</td>
                                <td>{{ $a->value }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted mb-0">This profile has no attributes.</p>
            @endif
        </div>
    </div>
@endsection
