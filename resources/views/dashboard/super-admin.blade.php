<x-app-layout>
    <x-slot name="header">{{ __('Super Admin Dashboard') }}</x-slot>

    <div class="container">
        <div class="alert alert-primary d-flex align-items-center" role="alert">
            <strong class="me-2">Super Admin</strong>
            <span>Full system control — user &amp; role management, billing configuration, and operator oversight.</span>
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">{{ __('Users & Roles') }}</h5>
                        <p class="card-text small text-muted">{{ __('Manage administrators and operators.') }}</p>
                        <a href="{{ route('users.index') }}" class="btn btn-outline-primary btn-sm">{{ __('Manage') }}</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">{{ __('System') }}</h5>
                        <p class="card-text small text-muted">{{ __('Health, queues, and integrations (MikroTik / RADIUS).') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">{{ __('Billing') }}</h5>
                        <p class="card-text small text-muted">{{ __('Not implemented yet.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
