<?php

namespace App\Models;

use App\Models\Concerns\ScopedToVessel;
use Illuminate\Database\Eloquent\Model;

class MaintenanceItem extends Model
{
    use ScopedToVessel;

    public const VESSEL_PARENT = 'checklist';

    protected $fillable = [
        'maintenance_checklist_id',
        'equipment',
        'item_no',
        'item_name',
        'task_description',
        'periodical_standard',
        'monitor_by',
        'remarks',
    ];

    public function checklist()
    {
        return $this->belongsTo(
            MaintenanceChecklist::class,
            'maintenance_checklist_id'
        );
    }

    public function dailyChecks()
    {
        return $this->hasMany(MaintenanceDailyCheck::class);
    }
}