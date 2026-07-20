<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Superadmin-only management of staff users (create / edit / delete) and roles.
 */
class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    /**
     * List all staff users with their roles.
     */
    public function index(): View
    {
        Gate::authorize('super_admin');

        return view('users.index', [
            'users' => $this->userService->listUsers(),
            'roles' => UserRole::cases(),
        ]);
    }

    /**
     * Show the create-user form.
     */
    public function create(): View
    {
        Gate::authorize('super_admin');

        return view('users.create', [
            'user' => new User,
            'roles' => UserRole::cases(),
        ]);
    }

    /**
     * Persist a new staff user.
     */
    public function store(UserRequest $request): RedirectResponse
    {
        Gate::authorize('super_admin');

        $user = $this->userService->createUser($request->validated());

        return redirect()
            ->route('users.index')
            ->with('status', "User \"{$user->name}\" created.");
    }

    /**
     * Show the edit-user form.
     */
    public function edit(User $user): View
    {
        Gate::authorize('super_admin');

        return view('users.edit', [
            'user' => $user,
            'roles' => UserRole::cases(),
        ]);
    }

    /**
     * Update an existing staff user (role / password).
     */
    public function update(UserRequest $request, User $user): RedirectResponse
    {
        Gate::authorize('super_admin');

        $this->userService->updateUser($user->id, $request->validated());

        return redirect()
            ->route('users.index')
            ->with('status', "User \"{$user->name}\" updated.");
    }

    /**
     * Delete a staff user. A user may not delete themselves.
     */
    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('super_admin');

        if ((string) $user->id === (string) auth()->id()) {
            return redirect()
                ->route('users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $name = $user->name;
        $this->userService->deleteUser($user->id);

        return redirect()
            ->route('users.index')
            ->with('status', "User \"{$name}\" deleted.");
    }
}
