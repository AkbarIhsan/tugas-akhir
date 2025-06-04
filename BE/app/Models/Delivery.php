<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    protected $table = 'delivery';
    protected $fillable = [
        'id_sales_order',
        'id_customer',
        'date',
        'status',
    ];
    public function customer(){
        return $this->belongsTo(Customer::class, 'id_customer');
    }

    public function salesOrder(){
        return $this->belongsTo(SalesOrder::class, 'id_sales_order');
    }
}
