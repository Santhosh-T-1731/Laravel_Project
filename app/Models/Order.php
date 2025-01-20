<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $table = 'orders';

    protected $guarded = [];

    public function status()
    {
        return $this->belongsTo(OrderStatus::class,'order_status_id','id');
    }

    public function orderDescriptions()
    {
        return $this->hasMany(OrderDescription::class);
    }

    public function products(){
        return $this->hasMany(Products::class);
    }

    public function address(){
        return $this->belongsTo(Address::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
