<?php

declare(strict_types=1);

namespace App\Models\Radius;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * FreeRADIUS `radacct` row — one accounting session (Start/Interim/Stop).
 *
 * Written by the RADIUS server via the `sql` accounting module. The app treats
 * this table as effectively read-only (reporting, usage totals, live sessions);
 * it should never be truncated by the application.
 *
 * @property int $radacctid
 * @property string $acctsessionid
 * @property string $acctuniqueid
 * @property string|null $username
 * @property string|null $nasipaddress
 * @property Carbon|null $acctstarttime
 * @property Carbon|null $acctstoptime
 * @property int|null $acctsessiontime
 * @property int|null $acctinputoctets
 * @property int|null $acctoutputoctets
 * @property string|null $callingstationid
 * @property string|null $framedipaddress
 */
class RadiusAccounting extends Model
{
    use HasFactory;

    protected $table = 'public.radacct';

    protected $primaryKey = 'radacctid';

    public $timestamps = false;

    protected $fillable = [
        'acctsessionid',
        'acctuniqueid',
        'username',
        'realm',
        'nasipaddress',
        'nasportid',
        'nasporttype',
        'acctstarttime',
        'acctupdatetime',
        'acctstoptime',
        'acctinterval',
        'acctsessiontime',
        'acctauthentic',
        'connectinfo_start',
        'connectinfo_stop',
        'acctinputoctets',
        'acctoutputoctets',
        'calledstationid',
        'callingstationid',
        'acctterminatecause',
        'servicetype',
        'framedprotocol',
        'framedipaddress',
    ];

    protected $casts = [
        'acctstarttime' => 'datetime',
        'acctupdatetime' => 'datetime',
        'acctstoptime' => 'datetime',
        'acctinterval' => 'integer',
        'acctsessiontime' => 'integer',
        'acctinputoctets' => 'integer',
        'acctoutputoctets' => 'integer',
    ];

    /** @param Builder<RadiusAccounting> $query */
    public function scopeForUsername(Builder $query, string $username): Builder
    {
        return $query->where('username', $username);
    }

    /**
     * Sessions with no stop time — currently online.
     *
     * @param  Builder<RadiusAccounting>  $query
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('acctstoptime');
    }

    /** @param Builder<RadiusAccounting> $query */
    public function scopeClosed(Builder $query): Builder
    {
        return $query->whereNotNull('acctstoptime');
    }

    /**
     * Total bytes transferred in this session (in + out).
     */
    public function totalOctets(): int
    {
        return (int) $this->acctinputoctets + (int) $this->acctoutputoctets;
    }
}
