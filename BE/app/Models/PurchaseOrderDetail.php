<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderDetail extends Model
{
    protected $table = 'purchase_order_detail';
    protected $fillable = [
        'id_purchase_order',
        'id_unit',
        'vendor',
        'qty',
        'cost_price',
        'total_price',
    ];

    public function purchaseOrder(){
        return $this->belongsTo(PurchaseOrder::class, 'id_purchase_order');
    }

    public function unit(){
        return $this->belongsTo(Unit::class, 'id_unit');
    }
}
