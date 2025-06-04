<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $table = 'purchase_order';
    protected $fillable = [
        'id_user',
        'date',
    ];

    public function purchaseOrderDetail(){
        return $this->hasMany(PurchaseOrderDetail::class, 'id_purchase_order');
    }
    public function users(){
        return $this->belongsTo(User::class, 'id_user');
    }
}
