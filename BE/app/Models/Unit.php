<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $table = 'unit';
    protected $fillable = [
        'id_product_type',
        'id_branch',
        'unit_name',
        'price',
        'cost_price',
        'stock',
        'min_stock',
    ];
    public function productType(){
        return $this->belongsTo(ProductType::class, 'id_product_type');
    }

    public function product(){
        return $this->productType->product; // Optional helper jika ingin akses langsung
    }

    public function branch(){
        return $this->belongsTo(Branch::class, 'id_branch');
    }

    public function purchaseOrderDetail(){
        return $this->hasMany(PurchaseOrderDetail::class, 'id_unit');
    }

    public function transferStockRequests()
    {
        return $this->hasMany(TransferStock::class, 'id_unit_request');
    }

    // Semua transfer stock yang menggunakan unit ini sebagai PENGIRIM (unit_gives)
    public function transferStockGives()
    {
        return $this->hasMany(TransferStock::class, 'id_unit_gives');
    }
}

