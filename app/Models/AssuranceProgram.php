<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssuranceProgram extends Model
{
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

    /**
     * Status => badge AdminLTE.
     */
    public const STATUS_BADGES = [

        'Done' => 'success',

        'Overdue' => 'danger',

        'Due Soon' => 'warning',

        'Open' => 'secondary',
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

    /**
     * Hari menuju tanggal rencana (negatif = lewat), seperti kolom "Due" di Excel.
     */
    public function daysToDue(): int
    {
        return (int) today()->diffInDays($this->planned_date, false);
    }

    public function status(): string
    {
        if ($this->actual_date) {
            return 'Done';
        }

        if ($this->daysToDue() < 0) {
            return 'Overdue';
        }

        return $this->daysToDue() <= Crew::WARNING_DAYS ? 'Due Soon' : 'Open';
    }

    public function location()
    {
        return $this->belongsTo(
            MasterLocation::class,
            'master_location_id'
        );
    }
}
