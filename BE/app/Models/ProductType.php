<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductType extends Model
{

    protected $table = 'product_type';
    protected $fillable = [
        'id_product',
        'product_name_type',
    ];
    public function product()
    {
        return $this->belongsTo(Product::class, 'id_product');
    }

    public function units()
    {
        return $this->hasMany(Unit::class, 'id_product_type');
    }
}
