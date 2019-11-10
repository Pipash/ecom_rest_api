<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';
    protected $guarded = ['id'];

    /**
     * Set date field for converting to date object in retrieve
     *
     * @var array
     */
    protected $dates = ['order_date', 'estimated_delivery_date', 'shipment_date'];

    public function products()
    {
        return $this->belongsToMany('App\Models\Product');
    }
}
