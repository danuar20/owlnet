<?php

declare(strict_types=1);

namespace App\Http\Controllers\Radius;

use App\Http\Controllers\Controller;
use App\Http\Requests\Radius\RouterRequest;
use App\Models\Radius\Router;
use App\Services\RouterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * CRUD + reachability tests for routers (NAS devices).
 *
 * Thin controller: validation via RouterRequest, all logic in RouterService.
 */
class RouterController extends Controller
{
    public function __construct(
        private readonly RouterService $routers
    ) {}

    public function index(): View
    {
        return view('radius.routers.index', [
            'routers' => $this->routers->list(),
        ]);
    }

    public function create(): View
    {
        return view('radius.routers.create', [
            'router' => new Router,
        ]);
    }

    public function store(RouterRequest $request): RedirectResponse
    {
        $router = $this->routers->create($request->validated());

        return redirect()
            ->route('routers.show', $router)
            ->with('status', "Router \"{$router->name}\" created.");
    }

    public function show(Router $router): View
    {
        return view('radius.routers.show', ['router' => $router]);
    }

    public function edit(Router $router): View
    {
        return view('radius.routers.edit', ['router' => $router]);
    }

    public function update(RouterRequest $request, Router $router): RedirectResponse
    {
        $updated = $this->routers->update($router->id, $request->validated());

        return redirect()
            ->route('routers.show', $updated)
            ->with('status', "Router \"{$updated->name}\" updated.");
    }

    public function destroy(Router $router): RedirectResponse
    {
        $this->routers->delete($router->id);

        return redirect()
            ->route('routers.index')
            ->with('status', "Router \"{$router->name}\" deleted.");
    }

    /**
     * AJAX ICMP ping test.
     */
    public function ping(Router $router): JsonResponse
    {
        $ms = $this->routers->ping($router);

        return response()->json([
            'ok' => $ms !== null,
            'ping_ms' => $ms,
            'message' => $ms !== null
                ? sprintf('Reachable (%.1f ms)', $ms)
                : 'No response to ICMP ping.',
        ]);
    }

    /**
     * AJAX TCP connection test to the API port; persists resulting status.
     */
    public function testConnection(Router $router): JsonResponse
    {
        $result = $this->routers->refreshStatus($router);

        return response()->json([
            'ok' => $result['api_reachable'],
            'status' => $result['status'],
            'ping_ms' => $result['ping_ms'],
            'api_reachable' => $result['api_reachable'],
            'message' => $result['api_reachable']
                ? 'API port reachable.'
                : 'Could not reach the router API port.',
        ]);
    }
}
