<?php

namespace App\Models;

use App\Models\Concerns\ScopedToVessel;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequestItem extends Model
{
    use ScopedToVessel;

    public const VESSEL_PARENT = 'purchaseRequest';

    protected $fillable = [

        'purchase_request_id',

        'description',

        'quantity',

        'unit',

        'unit_price',
    ];

    protected $casts = [

        'quantity' => 'float',

        'unit_price' => 'float',
    ];

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }
}
