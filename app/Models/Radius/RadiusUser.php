<?php

declare(strict_types=1);

namespace App\Models\Radius;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * FreeRADIUS `radcheck` row — a user authentication/check attribute.
 *
 * Each row is a single AVP (attribute-value pair) evaluated during the
 * authorize stage, e.g. (username, 'Cleartext-Password', ':=', 'secret').
 * A single RADIUS user is therefore represented by one or more radcheck rows.
 *
 * Maps to the live FreeRADIUS table in the `public` schema (shared with the
 * RADIUS server). The app search_path excludes `public`, so the table name is
 * fully qualified.
 *
 * @property int $id
 * @property string $username
 * @property string $attribute
 * @property string $op
 * @property string $value
 */
class RadiusUser extends Model
{
    use HasFactory;

    /** FreeRADIUS default password attribute. */
    public const PASSWORD_ATTRIBUTE = 'Cleartext-Password';

    protected $table = 'public.radcheck';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'username',
        'attribute',
        'op',
        'value',
    ];

    protected $attributes = [
        'attribute' => self::PASSWORD_ATTRIBUTE,
        'op' => ':=',
        'value' => '',
    ];

    /** @param Builder<RadiusUser> $query */
    public function scopeForUsername(Builder $query, string $username): Builder
    {
        return $query->where('username', $username);
    }

    /** @param Builder<RadiusUser> $query */
    public function scopeAttribute(Builder $query, string $attribute): Builder
    {
        return $query->where('attribute', $attribute);
    }

    /** @param Builder<RadiusUser> $query */
    public function scopePasswords(Builder $query): Builder
    {
        return $query->whereIn('attribute', [
            'Cleartext-Password',
            'Crypt-Password',
            'MD5-Password',
            'NT-Password',
            'SHA-Password',
        ]);
    }
}
