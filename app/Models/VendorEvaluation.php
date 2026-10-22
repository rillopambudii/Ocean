<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorEvaluation extends Model
{
    /**
     * Kolom skor => label kriteria.
     */
    public const CRITERIA = [

        'quality' => 'Kualitas',

        'delivery' => 'Ketepatan Waktu',

        'price' => 'Harga',

        'service' => 'Pelayanan',
    ];

    /**
     * Hasil => badge AdminLTE.
     */
    public const RESULT_BADGES = [

        'Baik' => 'success',

        'Cukup' => 'warning',

        'Kurang' => 'danger',
    ];

    protected $fillable = [

        'vendor_id',

        'review_date',

        'reviewer',

        'quality',

        'delivery',

        'price',

        'service',

        'remarks',

        'evidence_path',

        'evidence_name',
    ];

    protected $casts = [

        'review_date' => 'date',
    ];

    public function score(): float
    {
        return collect(array_keys(self::CRITERIA))->avg(fn ($column) => $this->{$column});
    }

    public function result(): string
    {
        return match (true) {
            $this->score() >= 4 => 'Baik',
            $this->score() >= 3 => 'Cukup',
            default => 'Kurang',
        };
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
