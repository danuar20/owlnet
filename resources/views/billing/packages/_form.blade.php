@php
    /** @var \App\Models\Billing\Package $package */
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('name') is-invalid @enderror"
               id="name" name="name" value="{{ old('name', $package->name) }}" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="code" class="form-label">Code</label>
        <input type="text" class="form-control @error('code') is-invalid @enderror"
               id="code" name="code" value="{{ old('code', $package->code) }}"
               placeholder="auto-generated if blank">
        @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="duration_days" class="form-label">Duration (days) <span class="text-danger">*</span></label>
        <input type="number" min="1" class="form-control @error('duration_days') is-invalid @enderror"
               id="duration_days" name="duration_days" value="{{ old('duration_days', $package->duration_days ?? 30) }}" required>
        @error('duration_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="speed_download" class="form-label">Speed Download</label>
        <input type="text" class="form-control @error('speed_download') is-invalid @enderror"
               id="speed_download" name="speed_download" value="{{ old('speed_download', $package->speed_download) }}"
               placeholder="10M">
        @error('speed_download') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="speed_upload" class="form-label">Speed Upload</label>
        <input type="text" class="form-control @error('speed_upload') is-invalid @enderror"
               id="speed_upload" name="speed_upload" value="{{ old('speed_upload', $package->speed_upload) }}"
               placeholder="10M">
        @error('speed_upload') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="price" class="form-label">Price (Rp) <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" class="form-control @error('price') is-invalid @enderror"
               id="price" name="price" value="{{ old('price', $package->price ?? 0) }}" required>
        @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="radius_profile" class="form-label">Radius Profile</label>
        <input type="text" class="form-control @error('radius_profile') is-invalid @enderror"
               id="radius_profile" name="radius_profile" value="{{ old('radius_profile', $package->radius_profile) }}"
               placeholder="FreeRADIUS group name (e.g. profile-10m)">
        @error('radius_profile') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control @error('description') is-invalid @enderror"
                  id="description" name="description" rows="3">{{ old('description', $package->description) }}</textarea>
        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <div class="form-check form-switch">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                   @checked(old('is_active', $package->is_active ?? true))>
            <label class="form-check-label" for="is_active">Active</label>
        </div>
    </div>
</div>
