<?php

namespace App\Models;

use App\Models\Concerns\ScopedToVessel;
use Illuminate\Database\Eloquent\Model;

class VesselCertificate extends Model
{
    use ScopedToVessel;

    protected $fillable = [

        'master_location_id',

        'certificate_name',

        'issue_place',

        'issued_date',

        'expired_date',

        'days_valid',

        'status',

        'remarks',

        'year',
    ];

    public function location()
    {
        return $this->belongsTo(
            MasterLocation::class,
            'master_location_id'
        );
    }
}