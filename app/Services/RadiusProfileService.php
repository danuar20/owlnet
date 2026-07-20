<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Radius\RadiusGroupReply;
use Illuminate\Support\Facades\DB;

/**
 * Manage FreeRADIUS "profiles" — groups defined by their radgroupreply
 * attributes (MikroTik-Rate-Limit, Session-Timeout, etc.).
 *
 * A profile is identified by its groupname. Members (customers) are linked
 * via radusergroup, which the Subscription module will write.
 */
class RadiusProfileService
{
    /**
     * List distinct group names that have reply attributes (i.e. defined profiles).
     *
     * @return \Illuminate\Support\Collection<int, string>
     */
    public function list(): \Illuminate\Support\Collection
    {
        return RadiusGroupReply::query()
            ->select('groupname')
            ->distinct()
            ->orderBy('groupname')
            ->pluck('groupname');
    }

    /**
     * @return array<int, RadiusGroupReply>
     */
    public function attributes(string $groupname): array
    {
        return RadiusGroupReply::forGroup($groupname)
            ->orderBy('attribute')
            ->get()
            ->all();
    }

    /**
     * @return array<string, string> attribute => value (first wins on dup)
     */
    public function attributesMap(string $groupname): array
    {
        return RadiusGroupReply::forGroup($groupname)
            ->pluck('value', 'attribute')
            ->all();
    }

    /**
     * Get all defined profiles as groupname => [attributes].
     *
     * @return array<string, array<int, RadiusGroupReply>>
     */
    public function allWithAttributes(): array
    {
        return RadiusGroupReply::query()
            ->orderBy('groupname')
            ->orderBy('attribute')
            ->get()
            ->groupBy('groupname')
            ->all();
    }

    /**
     * Create or replace a profile (groupname + its reply attributes).
     * Wrapped in a transaction; existing attributes for the group are removed.
     *
     * @param  array<int, array{attribute: string, op?: string, value: string}>  $attributes
     */
    public function saveProfile(string $groupname, array $attributes): void
    {
        DB::transaction(function () use ($groupname, $attributes): void {
            RadiusGroupReply::forGroup($groupname)->delete();

            foreach ($attributes as $attr) {
                if (empty($attr['attribute']) || empty($attr['value'])) {
                    continue;
                }

                RadiusGroupReply::create([
                    'groupname' => $groupname,
                    'attribute' => $attr['attribute'],
                    'op' => $attr['op'] ?? '=',
                    'value' => $attr['value'],
                ]);
            }
        });
    }

    public function deleteProfile(string $groupname): void
    {
        RadiusGroupReply::forGroup($groupname)->delete();
    }
}
