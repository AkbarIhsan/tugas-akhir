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
        'qty_product_request',
        'id_user_2',
        'status',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'id_user',);
    }

    public function user2(){
        return $this->belongsTo(User::class, 'id_user_2');
    }

    public function unit_request() {
        return $this->belongsTo(Unit::class, 'id_unit_request',);
    }

    public function unit_gives(){
        return $this->belongsTo(Unit::class, 'id_unit_gives');
    }

    // public function request(){
    //     return $this->hasMany(RequestModel::class, 'id_transfer_stock');
    // }
}
