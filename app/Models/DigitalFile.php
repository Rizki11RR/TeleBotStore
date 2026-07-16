<?php

namespace App\Models;

use App\Enums\DeliveryType;
use Illuminate\Database\Eloquent\Model;

class DigitalFile extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'product_variant_id',
        'delivery_type',
        'content',
        'file_path',
        'file_name',
        'notes',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'delivery_type' => DeliveryType::class,
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
