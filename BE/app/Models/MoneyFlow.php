<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MoneyFlow extends Model
{
    protected $table = 'money_flows';
    protected $fillable = [
        'id_flow_type',
        'id_user',
        'qty_money',
        'description',
        'date',
    ];

    public function flowType(){
        return $this->belongsTo(FlowType::class, 'id_flow_type');
    }

    public function users(){
        return $this->belongsTo(User::class, 'id_user');
    }
}
