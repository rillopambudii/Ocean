<?php

namespace App\Models\Concerns;

use App\Models\Crew;

/**
 * Kegiatan terjadwal dengan kolom planned_date & actual_date (cast date).
 * Status dihitung saat dibaca supaya selalu sesuai tanggal hari ini.
 */
trait HasPlannedSchedule
{
    /**
     * Status => badge AdminLTE.
     */
    public const STATUS_BADGES = [

        'Done' => 'success',

        'Overdue' => 'danger',

        'Due Soon' => 'warning',

        'Open' => 'secondary',
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
}
