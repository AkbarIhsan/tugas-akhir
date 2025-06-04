<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    protected $table = 'sales_order';
    protected $fillable = [
        'id_user',
        'date',
    ];

    public function salesOrderDetail(){
        return $this->hasMany(SalesOrderDetail::class, 'id_sales_order');
    }
    public function users(){
        return $this->belongsTo(User::class, 'id_user');
    }

    public function delivery(){
        return $this->belongsTo(Delivery::class, 'id_sales_order');
    }
}
