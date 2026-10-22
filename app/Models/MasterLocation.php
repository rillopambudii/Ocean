<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MasterLocation extends Model
{
    protected $fillable = [
        'area',
        'location',
        'name',
        'type',
    ];

    /**
     * Admin Kapal hanya melihat kapalnya sendiri, termasuk di dropdown form.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('vessel', function (Builder $query) {
            $vesselId = auth()->user()?->vesselScope();

            if ($vesselId !== null) {
                $query->whereKey($vesselId);
            }
        });
    }

    public function manHours()
    {
        return $this->hasMany(ManHour::class);
    }
}
