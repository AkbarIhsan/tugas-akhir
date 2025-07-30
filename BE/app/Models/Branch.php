<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $table = 'branch';
    protected $fillable = [
        'branch_name',
        'branch_address',
    ];
    public function users(){
        return $this->hasMany(User::class, 'id_branch');
    }

    public function units(){
        return $this->hasMany(Unit::class, 'id_branch');
    }


}
