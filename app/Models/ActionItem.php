<?php

namespace App\Models;

use App\Models\Concerns\ScopedToVessel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ActionItem extends Model
{
    use ScopedToVessel;

    /**
     * Departemen => [slug URL tampilan follow-up, prefix ID, judul tampilan].
     */
    public const DEPARTMENTS = [

        'QHSE' => ['slug' => 'qhse', 'prefix' => 'QHS', 'view' => 'QHSE Follow-up'],

        'Operation' => ['slug' => 'operation', 'prefix' => 'OPR', 'view' => 'Operational Follow-up'],

        'Technical' => ['slug' => 'technical', 'prefix' => 'TEC', 'view' => 'Repair Follow-up'],

        'Crewing' => ['slug' => 'crewing', 'prefix' => 'CRW', 'view' => 'Crewing Follow-up'],

        'Purchasing' => ['slug' => 'purchasing', 'prefix' => 'PCS', 'view' => 'Procurement Follow-up'],
    ];

    /**
     * Prioritas => badge AdminLTE.
     */
    public const PRIORITIES = [

        'High' => 'danger',

        'Medium' => 'warning',

        'Low' => 'info',
    ];

    /**
     * Status => badge AdminLTE.
     */
    public const STATUSES = [

        'Open' => 'secondary',

        'In Progress' => 'primary',

        'Closed' => 'success',
    ];

    protected $fillable = [

        'master_location_id',

        'title',

        'department',

        'lead_pic',

        'supporting_department',

        'priority',

        'target_date',

        'actual_date',

        'status',

        'next_action',

        'evidence_path',

        'evidence_name',
    ];

    protected $casts = [

        'target_date' => 'date',

        'actual_date' => 'date',
    ];

    public static function departmentBySlug(string $slug): ?string
    {
        foreach (self::DEPARTMENTS as $department => $config) {
            if ($config['slug'] === $slug) {
                return $department;
            }
        }

        return null;
    }

    /**
     * Aksi yang dipimpin atau didukung oleh departemen tsb.
     */
    public function scopeInvolving(Builder $query, string $department): Builder
    {
        return $query->where(fn ($query) => $query
            ->where('department', $department)
            ->orWhere('supporting_department', $department));
    }

    public function code(): string
    {
        return sprintf('%s-%04d', self::DEPARTMENTS[$this->department]['prefix'] ?? 'ACT', $this->id);
    }

    public function isOverdue(): bool
    {
        return $this->status !== 'Closed' && (bool) $this->target_date?->isBefore(today());
    }

    public function location()
    {
        return $this->belongsTo(
            MasterLocation::class,
            'master_location_id'
        );
    }
}
