@php
    /** @var \App\Models\Radius\Router $router */
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('name') is-invalid @enderror"
               id="name" name="name" value="{{ old('name', $router->name) }}" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="ip_address" class="form-label">IP Address <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('ip_address') is-invalid @enderror"
               id="ip_address" name="ip_address" value="{{ old('ip_address', $router->ip_address) }}"
               placeholder="192.168.10.1" required>
        @error('ip_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="radius_secret" class="form-label">Radius Secret</label>
        <input type="text" class="form-control @error('radius_secret') is-invalid @enderror"
               id="radius_secret" name="radius_secret" value="{{ old('radius_secret', $router->radius_secret) }}">
        @error('radius_secret') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="nas_identifier" class="form-label">NAS Identifier</label>
        <input type="text" class="form-control @error('nas_identifier') is-invalid @enderror"
               id="nas_identifier" name="nas_identifier" value="{{ old('nas_identifier', $router->nas_identifier) }}">
        @error('nas_identifier') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="location" class="form-label">Location</label>
        <input type="text" class="form-control @error('location') is-invalid @enderror"
               id="location" name="location" value="{{ old('location', $router->location) }}">
        @error('location') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label for="api_port" class="form-label">API Port</label>
        <input type="number" class="form-control @error('api_port') is-invalid @enderror"
               id="api_port" name="api_port" value="{{ old('api_port', $router->api_port ?? 8728) }}">
        @error('api_port') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label for="api_type" class="form-label">API Type</label>
        <select class="form-select" id="api_type" name="api_type">
            @foreach (['mikrotik', 'freeradius'] as $type)
                <option value="{{ $type }}" @selected(old('api_type', $router->api_type) === $type)>
                    {{ ucfirst($type) }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label for="username" class="form-label">API Username</label>
        <input type="text" class="form-control" id="username" name="username"
               value="{{ old('username', $router->username) }}" autocomplete="off">
    </div>

    <div class="col-md-6">
        <label for="password" class="form-label">API Password</label>
        <input type="password" class="form-control" id="password" name="password"
               value="{{ old('password') }}" autocomplete="new-password"
               placeholder="{{ $router->exists ? '•••••• (unchanged)' : '' }}">
    </div>

    <div class="col-md-6">
        <label for="status" class="form-label">Status</label>
        <select class="form-select" id="status" name="status">
            @foreach (\App\Models\Radius\Router::STATUSES as $s)
                <option value="{{ $s }}" @selected(old('status', $router->status) === $s)>
                    {{ ucfirst($s) }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6 d-flex align-items-end">
        <div class="form-check form-switch">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                   @checked(old('is_active', $router->is_active ?? true))>
            <label class="form-check-label" for="is_active">Active</label>
        </div>
    </div>

    <div class="col-12">
        <label for="remarks" class="form-label">Remarks</label>
        <textarea class="form-control" id="remarks" name="remarks" rows="2">{{ old('remarks', $router->remarks) }}</textarea>
    </div>
</div>
