<?php

declare(strict_types=1);

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\PackageRequest;
use App\Models\Billing\Package;
use App\Services\PackageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * CRUD for Internet packages (plans).
 *
 * Thin controller: validation via PackageRequest, logic in PackageService.
 */
class PackageController extends Controller
{
    public function __construct(
        private readonly PackageService $packages
    ) {}

    public function index(): View
    {
        return view('billing.packages.index', [
            'packages' => $this->packages->list(),
        ]);
    }

    public function create(): View
    {
        return view('billing.packages.create', [
            'package' => new Package,
            'profileOptions' => Package::radiusProfileOptions(),
        ]);
    }

    public function store(PackageRequest $request): RedirectResponse
    {
        $package = $this->packages->create($request->validated());

        return redirect()
            ->route('packages.show', $package)
            ->with('status', "Package \"{$package->name}\" created.");
    }

    public function show(Package $package): View
    {
        return view('billing.packages.show', ['package' => $package]);
    }

    public function edit(Package $package): View
    {
        return view('billing.packages.edit', [
            'package' => $package,
            'profileOptions' => Package::radiusProfileOptions(),
        ]);
    }

    public function update(PackageRequest $request, Package $package): RedirectResponse
    {
        $updated = $this->packages->update($package->id, $request->validated());

        return redirect()
            ->route('packages.show', $updated)
            ->with('status', "Package \"{$updated->name}\" updated.");
    }

    public function destroy(Package $package): RedirectResponse
    {
        $this->packages->delete($package->id);

        return redirect()
            ->route('packages.index')
            ->with('status', "Package \"{$package->name}\" deleted.");
    }
}
