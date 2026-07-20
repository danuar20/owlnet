<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Hermes ISP Billing') }}</title>

        <!-- Bootstrap 5 -->
        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
            rel="stylesheet"
            integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
            crossorigin="anonymous"
        >

        <!-- Application assets (compiled by Vite; optional project overrides) -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-body-tertiary">
        <div id="app">
            @auth
                <nav class="navbar navbar-expand-md navbar-dark bg-dark">
                    <div class="container">
                        <a class="navbar-brand brand-logo" href="{{ route('dashboard') }}">
                            {{ config('app.name', 'Hermes ISP Billing') }}
                        </a>
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                                data-bs-target="#mainNav" aria-controls="mainNav"
                                aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="mainNav">
                            <ul class="navbar-nav me-auto">
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('dashboard.*') ? 'active' : '' }}"
                                       href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('routers.*') ? 'active' : '' }}"
                                       href="{{ route('routers.index') }}">{{ __('Routers') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('packages.*') ? 'active' : '' }}"
                                       href="{{ route('packages.index') }}">{{ __('Packages') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('subscriptions.*') ? 'active' : '' }}"
                                       href="{{ route('subscriptions.index') }}">{{ __('Subscriptions') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}"
                                       href="{{ route('invoices.index') }}">{{ __('Invoices') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('radius-profiles.*') ? 'active' : '' }}"
                                       href="{{ route('radius-profiles.index') }}">{{ __('Radius Profiles') }}</a>
                                </li>
                            </ul>
                            <ul class="navbar-nav">
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" role="button"
                                       data-bs-toggle="dropdown" aria-expanded="false">
                                        {{ Auth::user()->name }}
                                        <span class="badge bg-secondary text-uppercase">
                                            {{ Auth::user()->role->label() }}
                                        </span>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                                {{ __('Profile') }}
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="{{ route('logout') }}">
                                                @csrf
                                                <button type="submit" class="dropdown-item">
                                                    {{ __('Log Out') }}
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
            @endauth

            <main class="py-4">
                @isset($header)
                    <div class="container">
                        <h1 class="h3 mb-3">{{ $header }}</h1>
                    </div>
                @endisset

                <div class="container">
                    @yield('content', $slot ?? '')
                </div>
            </main>
        </div>

        <!-- Bootstrap 5 JS bundle (dropdowns/modals) -->
        <script
            src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
            crossorigin="anonymous"
        ></script>

        @stack('scripts')
    </body>
</html>
