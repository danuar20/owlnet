<x-guest-layout>
    <div class="text-center">
        <h1 class="h3 mb-3">{{ config('app.name', 'Hermes ISP Billing') }}</h1>
        <p class="text-muted">{{ __('ISP Billing for MikroTik Hotspot & FreeRADIUS') }}</p>
        <a href="{{ route('login') }}" class="btn btn-primary mt-3">{{ __('Log in') }}</a>
    </div>
</x-guest-layout>
