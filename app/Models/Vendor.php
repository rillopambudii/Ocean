<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    /**
     * Approval status => badge AdminLTE.
     */
    public const APPROVAL_STATUSES = [

        'To Evaluate' => 'secondary',

        'Approved' => 'success',

        'Conditional' => 'warning',

        'Rejected' => 'danger',
    ];

    protected $fillable = [

        'name',

        'category',

        'contact',

        'approval_status',

        'remarks',
    ];

    public function code(): string
    {
        return sprintf('VEN-%04d', $this->id);
    }

    public function evaluations()
    {
        return $this->hasMany(VendorEvaluation::class)
            ->orderByDesc('review_date');
    }

    public function latestEvaluation()
    {
        return $this->hasOne(VendorEvaluation::class)
            ->latestOfMany('review_date');
    }
}
