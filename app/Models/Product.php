<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $guarded = ['id'];

    public function children()
    {
        return $this->hasMany(static::class, 'parent_product_id');
    }
    public function parent()
    {
        return $this->belongsTo(static::class, 'parent_product_id');
    }
    public function orders()
    {
        return $this->belongsToMany('App\Models\Order');
    }
}
