<?php

namespace App\Models;

use App\Models\Concerns\ScopedToVessel;
use Illuminate\Database\Eloquent\Model;

class VesselContract extends Model
{
    use ScopedToVessel;

    /**
     * Status kontrak => badge AdminLTE.
     */
    public const STATUS_BADGES = [

        'Active' => 'success',

        'Due ≤30 days' => 'danger',

        'Due 31–90 days' => 'warning',

        'Upcoming' => 'info',

        'Expired' => 'secondary',
    ];

    protected $fillable = [

        'master_location_id',

        'charterer',

        'start_date',

        'end_date',

        'remarks',
    ];

    protected $casts = [

        'start_date' => 'date',

        'end_date' => 'date',
    ];

    public function daysToEnd(): int
    {
        return (int) today()->diffInDays($this->end_date, false);
    }

    public function isCurrent(): bool
    {
        return $this->start_date->lte(today()) && $this->end_date->gte(today());
    }

    public function status(): string
    {
        if ($this->start_date->isAfter(today())) {
            return 'Upcoming';
        }

        $days = $this->daysToEnd();

        return match (true) {
            $days < 0 => 'Expired',
            $days <= 30 => 'Due ≤30 days',
            $days <= 90 => 'Due 31–90 days',
            default => 'Active',
        };
    }

    public function location()
    {
        return $this->belongsTo(
            MasterLocation::class,
            'master_location_id'
        );
    }
}
