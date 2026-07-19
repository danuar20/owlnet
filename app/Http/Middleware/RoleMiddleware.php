<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restrict routes to a specific user role (or any role of equal/higher rank).
 *
 * Register under the aliases `role`, `super_admin`, `admin`, and `operator`
 * in bootstrap/app.php so routes can do:
 *
 *   Route::get('/x', ...)->middleware('admin');          // admin OR super admin
 *   Route::get('/y', ...)->middleware('role:super_admin'); // exactly super admin
 *
 * A single role alias (super_admin|admin|operator) is expanded to include all
 * higher-ranked roles, matching the operational hierarchy:
 *   super_admin > admin > operator.
 */
class RoleMiddleware
{
    /**
     * Roles allowed for each single-role alias, most-privileged first.
     *
     * @var array<string, list<string>>
     */
    private const ALIAS_HIERARCHY = [
        'super_admin' => [UserRole::SUPER_ADMIN->value],
        'admin' => [UserRole::SUPER_ADMIN->value, UserRole::ADMIN->value],
        'operator' => [UserRole::SUPER_ADMIN->value, UserRole::ADMIN->value, UserRole::OPERATOR->value],
    ];

    /**
     * Handle an incoming request, allowing only the given role(s).
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        $allowed = $this->resolveAllowedRoles($roles);

        if (! in_array($user->role->value, $allowed, true)) {
            abort(403, 'This area requires the '.implode(', ', $allowed).' role.');
        }

        return $next($request);
    }

    /**
     * Expand the requested roles into an inclusive set of allowed role values.
     *
     * @param  list<string>  $roles
     * @return list<string>
     */
    private function resolveAllowedRoles(array $roles): array
    {
        $allowed = [];

        foreach ($roles as $role) {
            if (array_key_exists($role, self::ALIAS_HIERARCHY)) {
                $allowed = array_merge($allowed, self::ALIAS_HIERARCHY[$role]);

                continue;
            }

            // Explicit `role:foo` form — use the role verbatim (no hierarchy expansion).
            $allowed[] = UserRole::from($role)->value;
        }

        return array_values(array_unique($allowed));
    }
}
