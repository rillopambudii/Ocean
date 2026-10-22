<?php

namespace App\Models;

use App\Models\Concerns\ScopedToVessel;
use Illuminate\Database\Eloquent\Model;

class InternalAudit extends Model
{
    use ScopedToVessel;

    protected $fillable = [

        'audit_type',

        'audit_date',

        'master_location_id',

        'department',

        'auditor',

        'auditee',

        'status',

        'year',
    ];

    public function location()
    {
        return $this->belongsTo(
            MasterLocation::class,
            'master_location_id'
        );
    }

    public function findings()
    {
        return $this->hasMany(
            AuditFinding::class
        );
    }

    public function checklistAnswers()
    {
        return $this->hasMany(AuditChecklistAnswer::class);
    }
}