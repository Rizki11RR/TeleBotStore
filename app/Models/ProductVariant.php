<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'product_id',
        'name',
        'price',
        'original_price',
        'stock',
        'is_active',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'price'          => 'decimal:2',
        'original_price' => 'decimal:2',
        'stock'          => 'integer',
        'is_active'      => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function digitalFile()
    {
        return $this->hasOne(DigitalFile::class);
    }

    public function accounts()
    {
        return $this->hasMany(ProductVariantAccount::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /** Harga terformat dalam Rupiah */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp' . number_format($this->price, 0, ',', '.');
    }

    /** Harga asli terformat dalam Rupiah */
    public function getFormattedOriginalPriceAttribute(): ?string
    {
        return $this->original_price ? 'Rp' . number_format($this->original_price, 0, ',', '.') : null;
    }

    /** Cek stok tersedia (stock -1 = unlimited) */
    public function isAvailable(): bool
    {
        return $this->is_active && ($this->stock === -1 || $this->stock > 0);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
