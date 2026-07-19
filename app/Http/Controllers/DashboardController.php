<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * Role-scoped dashboard controller.
 *
 * Each action renders a Bootstrap dashboard. Business logic is intentionally
 * minimal here — this is the authentication module skeleton, not billing.
 */
class DashboardController extends Controller
{
    /**
     * Super admin dashboard.
     */
    public function superAdmin(): View
    {
        return view('dashboard.super-admin', [
            'role' => 'Super Admin',
        ]);
    }

    /**
     * Admin dashboard.
     */
    public function admin(): View
    {
        return view('dashboard.admin', [
            'role' => 'Admin',
        ]);
    }

    /**
     * Operator dashboard.
     */
    public function operator(): View
    {
        return view('dashboard.operator', [
            'role' => 'Operator',
        ]);
    }
}
