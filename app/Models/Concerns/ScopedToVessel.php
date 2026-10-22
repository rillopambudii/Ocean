<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Akun Admin Kapal hanya melihat dan mengisi data kapalnya sendiri.
 *
 * Model dengan kolom master_location_id difilter langsung; model anak
 * (mis. sertifikat kru) mendefinisikan VESSEL_PARENT dan ikut tersaring
 * lewat whereHas ke induknya, yang juga memakai trait ini.
 */
trait ScopedToVessel
{
    protected static function bootScopedToVessel(): void
    {
        static::addGlobalScope('vessel', function (Builder $query) {
            $vesselId = auth()->user()?->vesselScope();

            if ($vesselId === null) {
                return;
            }

            if (defined(static::class . '::VESSEL_PARENT')) {
                $query->whereHas(static::VESSEL_PARENT);
            } else {
                $query->where($query->getModel()->qualifyColumn('master_location_id'), $vesselId);
            }
        });

        static::saving(function ($model) {
            $vesselId = auth()->user()?->vesselScope();

            if ($vesselId === null) {
                return;
            }

            if (! defined(static::class . '::VESSEL_PARENT')) {
                $model->master_location_id = $vesselId;

                return;
            }

            // Induk dicari lewat relasi yang ikut tersaring: induk milik kapal lain = 404.
            abort_unless($model->{static::VESSEL_PARENT}()->exists(), 404);
        });
    }
}
