@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Radius Profiles</h1>
        <a href="{{ route('radius-profiles.create') }}" class="btn btn-primary">Add Profile</a>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header">FreeRADIUS Groups (radgroupreply)</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Profile (groupname)</th><th>Attributes</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse ($profiles as $groupname => $attrs)
                        <tr>
                            <td><code>{{ $groupname }}</code></td>
                            <td>
                                @foreach ($attrs as $a)
                                    <span class="badge bg-light text-dark border me-1">{{ $a->attribute }} = {{ $a->value }}</span>
                                @endforeach
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('radius-profiles.edit', $groupname) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form action="{{ route('radius-profiles.destroy', $groupname) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete profile {{ $groupname }}?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">
                                No profiles yet. <a href="{{ route('radius-profiles.create') }}">Create one</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
