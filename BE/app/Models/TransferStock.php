<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransferStock extends Model
{
    protected $table = 'transfer_stock';
    protected $fillable = [
        'id_user',
        'id_unit_request',
        'id_unit_gives',
        'product_price_gives',
        'qty_product_request',
        'total_price',
        'id_user_2',
    ];

    public function users() {
        return $this->belongsTo(User::class, 'id_user',);
    }

    public function user2(){
        return $this->belongsTo(User::class, 'id_user_2');
    }

    public function unit() {
        return $this->belongsTo(Unit::class, 'id_unit_request',);
    }

    public function units(){
        return $this->belongsTo(Unit::class, 'id_unit_gives');
    }

    public function request(){
        return $this->hasMany(RequestModel::class, 'id_transfer_stock');
    }
}
