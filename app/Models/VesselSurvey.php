<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VesselSurvey extends Model
{
    public const TYPES = [
        'Docking',
        'Annual Survey',
        'Intermediate Survey',
        'Special Survey',
        'Lainnya',
    ];

    /**
     * Badge AdminLTE per status jadwal.
     */
    public const STATUS_BADGES = [

        'Selesai' => 'success',

        'Terlambat' => 'danger',

        'Segera' => 'warning',

        'Terjadwal' => 'secondary',
    ];

    protected $fillable = [

        'master_location_id',

        'survey_type',

        'planned_date',

        'actual_date',

        'remarks',
    ];

    protected $casts = [

        'planned_date' => 'date',

        'actual_date' => 'date',
    ];

    /**
     * Status dihitung saat dibaca supaya selalu sesuai tanggal hari ini.
     */
    public function status(): string
    {
        if ($this->actual_date) {
            return 'Selesai';
        }

        if ($this->planned_date->isBefore(today())) {
            return 'Terlambat';
        }

        return $this->planned_date->lte(today()->addDays(Crew::WARNING_DAYS))
            ? 'Segera'
            : 'Terjadwal';
    }

    public function location()
    {
        return $this->belongsTo(
            MasterLocation::class,
            'master_location_id'
        );
    }
}
