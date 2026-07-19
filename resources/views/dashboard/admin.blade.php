<x-app-layout>
    <x-slot name="header">{{ __('Admin Dashboard') }}</x-slot>

    <div class="container">
        <div class="alert alert-info d-flex align-items-center" role="alert">
            <strong class="me-2">Admin</strong>
            <span>Operational management — packages, customers, and day-to-day billing operations.</span>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">{{ __('Operations') }}</h5>
                        <p class="card-text small text-muted">{{ __('Customers, vouchers, and session monitoring.') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">{{ __('Reports') }}</h5>
                        <p class="card-text small text-muted">{{ __('Usage and revenue reports (coming soon).') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
