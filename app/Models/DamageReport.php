<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DamageReport extends Model
{
    /**
     * Prioritas => badge AdminLTE.
     */
    public const PRIORITIES = [

        'High' => 'danger',

        'Medium' => 'warning',

        'Low' => 'info',
    ];

    public const STATUSES = ['Open', 'Closed'];

    protected $fillable = [

        'master_location_id',

        'reported_date',

        'title',

        'description',

        'priority',

        'status',

        'closed_date',

        'action_taken',
    ];

    protected $casts = [

        'reported_date' => 'date',

        'closed_date' => 'date',
    ];

    public function location()
    {
        return $this->belongsTo(
            MasterLocation::class,
            'master_location_id'
        );
    }
}
