<?php

declare(strict_types=1);

namespace App\Repositories\Radius;

use App\Models\Radius\RadiusGroup;
use App\Models\Radius\RadiusPostAuth;
use App\Models\Radius\RadiusReply;
use App\Models\Radius\RadiusUser;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * FreeRADIUS-compatible authentication repository (PostgreSQL).
 *
 * Reads/writes the canonical FreeRADIUS SQL schema so credentials managed by
 * the application interoperate directly with the RADIUS server. Password
 * verification mirrors FreeRADIUS's rlm_pap normalisation for the common
 * password attributes.
 */
class RadiusAuthRepository implements RadiusAuthRepositoryInterface
{
    /** Password attributes understood by verifyCredentials(), in priority order. */
    private const PASSWORD_ATTRIBUTES = [
        'Cleartext-Password',
        'MD5-Password',
        'SHA-Password',
        'SHA1-Password',
        'Crypt-Password',
        'NT-Password',
    ];

    /**
     * {@inheritDoc}
     */
    public function checkAttributes(string $username): Collection
    {
        return RadiusUser::forUsername($username)->orderBy('id')->get();
    }

    /**
     * {@inheritDoc}
     */
    public function passwordAttribute(string $username): ?RadiusUser
    {
        return RadiusUser::forUsername($username)
            ->whereIn('attribute', self::PASSWORD_ATTRIBUTES)
            ->orderByRaw($this->passwordPriorityExpression())
            ->first();
    }

    /**
     * {@inheritDoc}
     */
    public function exists(string $username): bool
    {
        return RadiusUser::forUsername($username)->exists();
    }

    /**
     * {@inheritDoc}
     */
    public function verifyCredentials(string $username, string $password): bool
    {
        $row = $this->passwordAttribute($username);

        if ($row === null) {
            return false;
        }

        return $this->passwordMatches($row->attribute, $row->value, $password);
    }

    /**
     * {@inheritDoc}
     */
    public function setPassword(
        string $username,
        string $password,
        string $attribute = RadiusUser::PASSWORD_ATTRIBUTE
    ): RadiusUser {
        $value = $this->encodePassword($attribute, $password);

        // Remove any existing password attributes so only one remains.
        RadiusUser::forUsername($username)
            ->whereIn('attribute', self::PASSWORD_ATTRIBUTES)
            ->delete();

        return RadiusUser::create([
            'username' => $username,
            'attribute' => $attribute,
            'op' => ':=',
            'value' => $value,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function setCheckAttribute(
        string $username,
        string $attribute,
        string $value,
        string $op = ':='
    ): RadiusUser {
        $row = RadiusUser::forUsername($username)
            ->where('attribute', $attribute)
            ->first();

        if ($row !== null) {
            $row->update(['op' => $op, 'value' => $value]);

            return $row->refresh();
        }

        return RadiusUser::create([
            'username' => $username,
            'attribute' => $attribute,
            'op' => $op,
            'value' => $value,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteUser(string $username): int
    {
        $deleted = RadiusUser::forUsername($username)->delete();
        $deleted += RadiusReply::forUsername($username)->delete();
        $deleted += RadiusGroup::forUsername($username)->delete();

        return $deleted;
    }

    /**
     * {@inheritDoc}
     */
    public function recordPostAuth(
        string $username,
        string $reply,
        ?string $password = null,
        ?string $callingStationId = null
    ): RadiusPostAuth {
        return RadiusPostAuth::create([
            'username' => $username,
            'pass' => $password,
            'reply' => $reply,
            'callingstationid' => $callingStationId,
            'authdate' => Carbon::now(),
        ]);
    }

    /**
     * Encode a plaintext password for the given FreeRADIUS attribute.
     */
    private function encodePassword(string $attribute, string $password): string
    {
        return match ($attribute) {
            'Cleartext-Password' => $password,
            'MD5-Password' => md5($password),
            'SHA-Password', 'SHA1-Password' => sha1($password),
            'NT-Password' => strtoupper(bin2hex(hash('md4', mb_convert_encoding($password, 'UTF-16LE'), true))),
            'Crypt-Password' => crypt($password, $password),
            default => $password,
        };
    }

    /**
     * Compare a plaintext password against a stored FreeRADIUS value.
     */
    private function passwordMatches(string $attribute, string $stored, string $password): bool
    {
        return match ($attribute) {
            'Cleartext-Password' => hash_equals($stored, $password),
            'MD5-Password' => hash_equals(strtolower($stored), md5($password)),
            'SHA-Password', 'SHA1-Password' => hash_equals(strtolower($stored), sha1($password)),
            'NT-Password' => hash_equals(
                strtoupper($stored),
                strtoupper(bin2hex(hash('md4', mb_convert_encoding($password, 'UTF-16LE'), true)))
            ),
            'Crypt-Password' => hash_equals($stored, (string) crypt($password, $stored)),
            default => false,
        };
    }

    /**
     * SQL CASE expression ordering password rows by our attribute priority.
     */
    private function passwordPriorityExpression(): string
    {
        $cases = [];
        foreach (array_values(self::PASSWORD_ATTRIBUTES) as $index => $attribute) {
            $cases[] = "WHEN attribute = '{$attribute}' THEN {$index}";
        }

        return 'CASE '.implode(' ', $cases).' ELSE 99 END';
    }
}
