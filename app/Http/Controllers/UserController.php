<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;

/**
 * Example controller demonstrating the project's layering convention.
 *
 * Business logic lives in the service layer; the controller only validates
 * the request and delegates to a service. No Eloquent queries here.
 */
class UserController extends Controller
{
    /**
     * Create the controller with its required service dependency.
     */
    public function __construct(
        private readonly UserService $userService
    ) {}

    /**
     * Display a listing of users via the service layer.
     */
    public function index(Request $request)
    {
        $users = $this->userService->listUsers();

        return view('users.index', compact('users'));
    }
}
