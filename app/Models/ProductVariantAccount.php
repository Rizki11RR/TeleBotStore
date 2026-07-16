<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariantAccount extends Model
{
    protected $fillable = [
        'product_variant_id',
        'username_email',
        'password',
        'is_sold',
        'sold_at',
        'order_id',
    ];

    protected $casts = [
        'is_sold' => 'boolean',
        'sold_at' => 'datetime',
    ];

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
