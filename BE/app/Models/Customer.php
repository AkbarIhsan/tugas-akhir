<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customer';
    protected $fillable = [
        'name',
        'customer_address',
        'phone'
    ];
    public function delivery(){
        return $this->hasMany(Delivery::class, 'id_customer');
    }
}
