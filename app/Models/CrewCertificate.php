<?php

namespace App\Models;

use App\Models\Concerns\ScopedToVessel;
use Illuminate\Database\Eloquent\Model;

class CrewCertificate extends Model
{
    use ScopedToVessel;

    public const VESSEL_PARENT = 'crew';

    protected $fillable = [

        'crew_id',

        'name',

        'certificate_number',

        'expired_date',
    ];

    protected $casts = [

        'expired_date' => 'date',
    ];

    public function status(): ?string
    {
        return Crew::expiryStatus($this->expired_date);
    }

    public function crew()
    {
        return $this->belongsTo(Crew::class);
    }
}
