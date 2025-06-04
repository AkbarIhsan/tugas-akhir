<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlowType extends Model
{
    public function moneyFlows(){
        return $this->hasMany(MoneyFlow::class, 'id_flow_type');
    }
}
