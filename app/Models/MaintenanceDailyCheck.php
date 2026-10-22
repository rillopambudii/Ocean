<?php

namespace App\Models;

use App\Models\Concerns\ScopedToVessel;
use Illuminate\Database\Eloquent\Model;

class MaintenanceDailyCheck extends Model
{
    use ScopedToVessel;

    public const VESSEL_PARENT = 'item';

    protected $fillable = [
        'maintenance_item_id',
        'check_date',
        'status',
        'note',
    ];

    public function item()
    {
        return $this->belongsTo(
            MaintenanceItem::class,
            'maintenance_item_id'
        );
    }
}