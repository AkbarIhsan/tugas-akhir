<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $table = 'branch';
    public function users(){
        return $this->hasMany(User::class, 'id_branch');
    }

    public function units(){
        return $this->hasMany(Unit::class, 'id_branch');
    }


}
