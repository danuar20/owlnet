<x-app-layout>
    <x-slot name="header">
        {{ __('Verify Email') }}
    </x-slot>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <p class="text-muted small">
                            {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
                        </p>

                        @if (session('status') == 'verification-link-sent')
                            <div class="alert alert-success small">
                                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                            </div>
                        @endif

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <form method="POST" action="{{ route('verification.send') }}">
                                @csrf
                                <x-primary-button>
                                    {{ __('Resend Verification Email') }}
                                </x-primary-button>
                            </form>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-link small text-decoration-none">
                                    {{ __('Log Out') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
