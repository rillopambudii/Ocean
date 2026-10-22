<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequest extends Model
{
    /**
     * Kategori pembelian — dipakai juga sebagai pos anggaran di modul Budget.
     */
    public const CATEGORIES = [
        'Spare Part',
        'Consumable',
        'Bahan Bakar & Pelumas',
        'Safety Equipment',
        'Docking & Repair',
        'Provision',
        'Kantor',
        'Lainnya',
    ];

    /**
     * Status (urut sesuai alur) => badge AdminLTE.
     */
    public const STATUSES = [

        'Diajukan' => 'secondary',

        'Disetujui' => 'info',

        'Dipesan' => 'primary',

        'Diterima' => 'success',

        'Ditolak' => 'danger',
    ];

    protected $fillable = [

        'request_number',

        'master_location_id',

        'title',

        'category',

        'request_date',

        'status',

        'vendor',

        'po_number',

        'received_date',

        'attachment_path',

        'attachment_name',

        'remarks',

        'requested_by',
    ];

    protected $casts = [

        'request_date' => 'date',

        'received_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::created(function (PurchaseRequest $request) {
            $request->updateQuietly([
                'request_number' => sprintf('PR-%s-%04d', $request->request_date->format('Y'), $request->id),
            ]);
        });
    }

    public function total(): float
    {
        return $this->items->sum(fn ($item) => $item->quantity * $item->unit_price);
    }

    public function location()
    {
        return $this->belongsTo(
            MasterLocation::class,
            'master_location_id'
        );
    }

    public function requester()
    {
        return $this->belongsTo(
            User::class,
            'requested_by'
        );
    }

    public function items()
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }
}
