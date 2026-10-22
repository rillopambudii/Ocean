<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequestItem extends Model
{
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
