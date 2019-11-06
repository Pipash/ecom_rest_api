<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $guarded = ['id'];

    public function children()
    {
        $this->hasMany('Product', 'parent_product_id');
    }
    public function parent()
    {
        $this->belongsTo('Product', 'parent_product_id');
    }
}
