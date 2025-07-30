<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestModel extends Model
{
    protected $table = 'request';
    protected $fillable = [
        'id_transfer_stock',
        'status',
        'date',
    ];

    // public function transferStock(){
    //     return $this->belongsTo(TransferStock::class, 'id_transfer_stock');
    // }
}
