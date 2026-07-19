<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Radius\RadiusAccounting;
use App\Models\Radius\RadiusGroup;
use App\Models\Radius\RadiusPostAuth;
use App\Models\Radius\RadiusReply;
use App\Models\Radius\RadiusUser;
use App\Repositories\Radius\RadiusAuthRepositoryInterface;
use Illuminate\Support\Collection;

/**
 * Domain service for FreeRADIUS operations (PostgreSQL back-end).
 *
 * Orchestrates the authentication repository and the RADIUS Eloquent models.
 * No MikroTik / RouterOS integration is performed here — this layer only
 * manages the FreeRADIUS SQL schema and derives reporting data from it.
 */
class RadiusService extends BaseService
{
    public function __construct(
        private readonly RadiusAuthRepositoryInterface $auth
    ) {}

    /**
     * Provision (create or update) a RADIUS user: password, optional group,
     * and optional reply attributes.
     *
     * @param  array<string, string>  $replyAttributes  attribute => value
     */
    public function createUser(
        string $username,
        string $password,
        ?string $group = null,
        array $replyAttributes = [],
        string $passwordAttribute = RadiusUser::PASSWORD_ATTRIBUTE
    ): RadiusUser {
        $user = $this->auth->setPassword($username, $password, $passwordAttribute);

        if ($group !== null) {
            $this->assignGroup($username, $group);
        }

        foreach ($replyAttributes as $attribute => $value) {
            $this->setReplyAttribute($username, $attribute, $value);
        }

        return $user;
    }

    /**
     * Change an existing user's password.
     */
    public function updatePassword(
        string $username,
        string $password,
        string $passwordAttribute = RadiusUser::PASSWORD_ATTRIBUTE
    ): RadiusUser {
        return $this->auth->setPassword($username, $password, $passwordAttribute);
    }

    /**
     * Authenticate a user and record the attempt in radpostauth.
     */
    public function authenticate(
        string $username,
        string $password,
        ?string $callingStationId = null
    ): bool {
        $ok = $this->auth->verifyCredentials($username, $password);

        $this->auth->recordPostAuth(
            $username,
            $ok ? RadiusPostAuth::REPLY_ACCEPT : RadiusPostAuth::REPLY_REJECT,
            null,
            $callingStationId
        );

        return $ok;
    }

    /**
     * Whether a RADIUS user exists.
     */
    public function userExists(string $username): bool
    {
        return $this->auth->exists($username);
    }

    /**
     * Delete a user and all of their radcheck/radreply/radusergroup rows.
     */
    public function deleteUser(string $username): int
    {
        return $this->auth->deleteUser($username);
    }

    /**
     * Assign a user to a group (idempotent per group name).
     */
    public function assignGroup(string $username, string $groupname, int $priority = 1): RadiusGroup
    {
        $existing = RadiusGroup::forUsername($username)
            ->forGroup($groupname)
            ->first();

        if ($existing !== null) {
            $existing->update(['priority' => $priority]);

            return $existing->refresh();
        }

        return RadiusGroup::create([
            'username' => $username,
            'groupname' => $groupname,
            'priority' => $priority,
        ]);
    }

    /**
     * Remove a user from a group.
     */
    public function removeFromGroup(string $username, string $groupname): int
    {
        return RadiusGroup::forUsername($username)->forGroup($groupname)->delete();
    }

    /**
     * Add or update a per-user reply attribute (radreply).
     */
    public function setReplyAttribute(
        string $username,
        string $attribute,
        string $value,
        string $op = '='
    ): RadiusReply {
        $row = RadiusReply::forUsername($username)->where('attribute', $attribute)->first();

        if ($row !== null) {
            $row->update(['op' => $op, 'value' => $value]);

            return $row->refresh();
        }

        return RadiusReply::create([
            'username' => $username,
            'attribute' => $attribute,
            'op' => $op,
            'value' => $value,
        ]);
    }

    /**
     * Currently online sessions (radacct rows with no stop time).
     *
     * @return Collection<int, RadiusAccounting>
     */
    public function activeSessions(?string $username = null): Collection
    {
        $query = RadiusAccounting::query()->active();

        if ($username !== null) {
            $query->forUsername($username);
        }

        return $query->orderByDesc('acctstarttime')->get();
    }

    /**
     * Total bytes (in + out) consumed by a user across all sessions.
     */
    public function totalUsage(string $username): int
    {
        $row = RadiusAccounting::forUsername($username)
            ->selectRaw('COALESCE(SUM(acctinputoctets),0) AS in_bytes, COALESCE(SUM(acctoutputoctets),0) AS out_bytes')
            ->first();

        return (int) ($row->in_bytes ?? 0) + (int) ($row->out_bytes ?? 0);
    }

    /**
     * Recent authentication attempts for a user (most recent first).
     *
     * @return Collection<int, RadiusPostAuth>
     */
    public function authHistory(string $username, int $limit = 20): Collection
    {
        return RadiusPostAuth::forUsername($username)
            ->orderByDesc('authdate')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }
}
