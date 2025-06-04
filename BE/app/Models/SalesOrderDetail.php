<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrderDetail extends Model
{
    protected $table = 'sales_order_detail';
    protected $fillable = [
        'id_sales_order',
        'id_unit',
        'qty',
        'price',
        'total_price',
    ];

    public function salesOrder(){
        return $this->belongsTo(SalesOrder::class, 'id_sales_order');
    }

    public function unit(){
        return $this->belongsTo(Unit::class, 'id_unit');
    }
}
