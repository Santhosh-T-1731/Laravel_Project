<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDescription extends Model
{
    protected $table = 'order_description';

    protected $guarded = [];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function products(){
        return $this->hasMany(Products::class,'product_id','id');
    }
}
