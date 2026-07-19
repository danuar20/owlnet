<?php

declare(strict_types=1);

namespace App\Repositories\Radius;

use App\Models\Radius\RadiusPostAuth;
use App\Models\Radius\RadiusUser;
use Illuminate\Support\Collection;

/**
 * Contract for FreeRADIUS-compatible authentication persistence.
 *
 * Implementations read and write the standard FreeRADIUS SQL schema
 * (radcheck / radreply / radusergroup / radpostauth) so that credentials
 * created through the application are immediately usable by the RADIUS server,
 * and vice versa.
 */
interface RadiusAuthRepositoryInterface
{
    /**
     * Return every check attribute row for a username.
     *
     * @return Collection<int, RadiusUser>
     */
    public function checkAttributes(string $username): Collection;

    /**
     * Return the stored password attribute row for a username, or null.
     */
    public function passwordAttribute(string $username): ?RadiusUser;

    /**
     * Determine whether a username exists in radcheck.
     */
    public function exists(string $username): bool;

    /**
     * Verify a plaintext password against the stored FreeRADIUS password
     * attribute (Cleartext-, MD5-, SHA-, Crypt-, NT-Password).
     */
    public function verifyCredentials(string $username, string $password): bool;

    /**
     * Create (or replace) a user's password attribute in radcheck.
     */
    public function setPassword(
        string $username,
        string $password,
        string $attribute = RadiusUser::PASSWORD_ATTRIBUTE
    ): RadiusUser;

    /**
     * Add or update an arbitrary check attribute (op defaults to ':=').
     */
    public function setCheckAttribute(
        string $username,
        string $attribute,
        string $value,
        string $op = ':='
    ): RadiusUser;

    /**
     * Remove a user entirely (radcheck + radreply + radusergroup rows).
     */
    public function deleteUser(string $username): int;

    /**
     * Record an authentication attempt in radpostauth.
     */
    public function recordPostAuth(
        string $username,
        string $reply,
        ?string $password = null,
        ?string $callingStationId = null
    ): RadiusPostAuth;
}
