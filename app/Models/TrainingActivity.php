<?php

namespace App\Models;

use App\Models\Concerns\HasPlannedSchedule;
use Illuminate\Database\Eloquent\Model;

class TrainingActivity extends Model
{
    use HasPlannedSchedule;

    /**
     * Jenis kegiatan => prefix ID — sesuai KPI di QHSE Statistic 2026.xlsx.
     */
    public const TYPES = [

        'QHSE Training' => 'TRN',

        'In-house Training & Sharing Awareness' => 'TRN',

        'Shipboard Safety Familiarization' => 'FAM',

        'Emergency Drill' => 'DRL',
    ];

    protected $fillable = [

        'master_location_id',

        'activity_type',

        'topic',

        'planned_date',

        'actual_date',

        'participants',

        'instructor',

        'evidence_path',

        'evidence_name',
    ];

    protected $casts = [

        'planned_date' => 'date',

        'actual_date' => 'date',
    ];

    public function code(): string
    {
        return sprintf('%s-%04d', self::TYPES[$this->activity_type] ?? 'ACT', $this->id);
    }

    public function location()
    {
        return $this->belongsTo(
            MasterLocation::class,
            'master_location_id'
        );
    }
}
