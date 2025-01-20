<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';

    protected $guarded = [];

    public function parent()
    {
        return $this->hasOne(self::class,'id','parent_id');
    }

    public function products(){
        return $this->hasMany(Products::class);
    }

    public function subCategories(){
        return $this->hasMany(self::class,'parent_id','id');
    }


}
