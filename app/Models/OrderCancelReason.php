<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderCancelReason extends Model
{
    protected $table = 'order_cancel_reason';

    protected $guarded = [];

    public function order(){
        return $this->belongsTo(Order::class);
    }
}
