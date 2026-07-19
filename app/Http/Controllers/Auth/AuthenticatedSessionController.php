<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended($this->redirectToDashboard($request));
    }

    /**
     * Resolve the post-login dashboard route based on the user's role.
     */
    protected function redirectToDashboard(Request $request): string
    {
        /** @var User $user */
        $user = $request->user();

        return match (true) {
            $user->isSuperAdmin() => route('dashboard.super-admin', absolute: false),
            $user->isAdmin() => route('dashboard.admin', absolute: false),
            default => route('dashboard.operator', absolute: false),
        };
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
