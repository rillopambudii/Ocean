<?php

namespace App\Models;

use App\Models\Concerns\ScopedToVessel;
use App\Models\Concerns\HasPlannedSchedule;
use Illuminate\Database\Eloquent\Model;

class AssuranceProgram extends Model
{
    use HasPlannedSchedule, ScopedToVessel;

    /**
     * Jenis assurance — dari sheet "Data Source" di Sub Menu Qhse.xlsx.
     */
    public const TYPES = [
        'Management Visit',
        'Superintendent Visit',
        'Internal Audit',
        'ISO Certification',
        'ISM Code DOC',
        'ISM Code SMC',
        'ISPS Code ISSC',
        'Management Review',
        'Master Review',
    ];

    protected $fillable = [

        'master_location_id',

        'assurance_type',

        'planned_date',

        'actual_date',

        'remarks',

        'nc_open',

        'nc_closed',

        'evidence_path',

        'evidence_name',
    ];

    protected $casts = [

        'planned_date' => 'date',

        'actual_date' => 'date',
    ];

    public function location()
    {
        return $this->belongsTo(
            MasterLocation::class,
            'master_location_id'
        );
    }
}
