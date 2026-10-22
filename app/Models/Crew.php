<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Crew extends Model
{
    /**
     * Batas hari sebelum tanggal habis dianggap "Segera Habis".
     */
    public const WARNING_DAYS = 30;

    /**
     * Badge AdminLTE per status masa berlaku.
     */
    public const STATUS_BADGES = [

        'Expired' => 'danger',

        'Segera Habis' => 'warning',

        'Aktif' => 'success',
    ];

    protected $fillable = [

        'master_location_id',

        'name',

        'rank',

        'seaman_book_number',

        'sign_on_date',

        'sign_off_date',

        'remarks',
    ];

    protected $casts = [

        'sign_on_date' => 'date',

        'sign_off_date' => 'date',
    ];

    /**
     * Status masa berlaku dihitung saat dibaca, supaya tidak basi seperti kolom tersimpan.
     */
    public static function expiryStatus(?Carbon $date): ?string
    {
        if (! $date) {
            return null;
        }

        if ($date->isBefore(today())) {
            return 'Expired';
        }

        return $date->lte(today()->addDays(self::WARNING_DAYS))
            ? 'Segera Habis'
            : 'Aktif';
    }

    public function contractStatus(): ?string
    {
        return self::expiryStatus($this->sign_off_date);
    }

    public function location()
    {
        return $this->belongsTo(
            MasterLocation::class,
            'master_location_id'
        );
    }

    public function certificates()
    {
        return $this->hasMany(CrewCertificate::class)
            ->orderBy('expired_date');
    }
}
