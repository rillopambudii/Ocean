<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SafetyObservation extends Model
{
    /**
     * Kategori OSCard => badge AdminLTE.
     */
    public const CATEGORIES = [

        'Unsafe Act' => 'danger',

        'Unsafe Condition' => 'warning',

        'Near Miss' => 'info',

        'Good Practice' => 'success',
    ];

    public const STATUSES = ['Open', 'Closed'];

    protected $fillable = [

        'master_location_id',

        'observed_date',

        'category',

        'description',

        'immediate_action',

        'pic',

        'due_date',

        'status',

        'closed_date',

        'evidence_path',

        'evidence_name',
    ];

    protected $casts = [

        'observed_date' => 'date',

        'due_date' => 'date',

        'closed_date' => 'date',
    ];

    public function code(): string
    {
        return sprintf('OBS-%04d', $this->id);
    }

    public function isOverdue(): bool
    {
        return $this->status === 'Open' && $this->due_date?->isBefore(today());
    }

    public function location()
    {
        return $this->belongsTo(
            MasterLocation::class,
            'master_location_id'
        );
    }
}
