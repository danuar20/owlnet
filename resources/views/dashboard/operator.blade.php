<x-app-layout>
    <x-slot name="header">{{ __('Operator Dashboard') }}</x-slot>

    <div class="container">
        <div class="alert alert-secondary d-flex align-items-center" role="alert">
            <strong class="me-2">Operator</strong>
            <span>Helpdesk &amp; front-line operations — assist customers and manage active sessions.</span>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">{{ __('Customer Support') }}</h5>
                        <p class="card-text small text-muted">{{ __('Look up customers and resolve connectivity issues.') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">{{ __('Vouchers') }}</h5>
                        <p class="card-text small text-muted">{{ __('Issue and redeem vouchers (coming soon).') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
