<?php

declare(strict_types=1);

namespace App\Http\Controllers\Radius;

use App\Http\Controllers\Controller;
use App\Http\Requests\Radius\RadiusProfileRequest;
use App\Services\RadiusProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * CRUD for FreeRADIUS profiles (groups of radgroupreply attributes).
 */
class RadiusProfileController extends Controller
{
    public function __construct(
        private readonly RadiusProfileService $profiles
    ) {}

    public function index(): View
    {
        return view('radius.profiles.index', [
            'profiles' => $this->profiles->allWithAttributes(),
        ]);
    }

    public function create(): View
    {
        return view('radius.profiles.create', [
            'groupname' => '',
            'attributes' => [['attribute' => 'Mikrotik-Rate-Limit', 'op' => ':=', 'value' => '2048k/4096k']],
        ]);
    }

    public function store(RadiusProfileRequest $request): RedirectResponse
    {
        $groupname = $request->input('groupname');
        $this->profiles->saveProfile($groupname, $request->input('attributes', []));

        return redirect()
            ->route('radius-profiles.show', $groupname)
            ->with('status', "Profile \"{$groupname}\" saved.");
    }

    public function show(string $groupname): View
    {
        return view('radius.profiles.show', [
            'groupname' => $groupname,
            'attributes' => $this->profiles->attributes($groupname),
        ]);
    }

    public function edit(string $groupname): View
    {
        $attrs = $this->profiles->attributes($groupname);
        $attributes = $attrs === [] ? [] : array_map(
            fn ($a) => ['attribute' => $a->attribute, 'op' => $a->op, 'value' => $a->value],
            $attrs
        );

        return view('radius.profiles.edit', [
            'groupname' => $groupname,
            'attributes' => $attributes,
        ]);
    }

    public function update(RadiusProfileRequest $request, string $groupname): RedirectResponse
    {
        $newName = $request->input('groupname');
        // if the group name changed, drop the old one first
        if ($newName !== $groupname) {
            $this->profiles->deleteProfile($groupname);
        }
        $this->profiles->saveProfile($newName, $request->input('attributes', []));

        return redirect()
            ->route('radius-profiles.show', $newName)
            ->with('status', "Profile \"{$newName}\" updated.");
    }

    public function destroy(string $groupname): RedirectResponse
    {
        $this->profiles->deleteProfile($groupname);

        return redirect()
            ->route('radius-profiles.index')
            ->with('status', "Profile \"{$groupname}\" deleted.");
    }
}
