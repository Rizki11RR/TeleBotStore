<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'invoice_number',
        'telegram_user_id',
        'product_variant_id',
        'quantity',
        'total_price',
        'status',
        'notes',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'status'      => OrderStatus::class,
        'total_price' => 'decimal:2',
        'quantity'    => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function telegramUser()
    {
        return $this->belongsTo(TelegramUser::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /** Generate nomor invoice otomatis */
    public static function generateInvoiceNumber(): string
    {
        $date = now()->format('Ymd');
        $last = static::whereDate('created_at', today())
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $seq = $last ? ((int) substr($last, -6)) + 1 : 1;

        return 'INV-' . $date . '-' . str_pad($seq, 6, '0', STR_PAD_LEFT);
    }

    /** Total harga terformat */
    public function getFormattedTotalPriceAttribute(): string
    {
        return 'Rp' . number_format($this->total_price, 0, ',', '.');
    }
}
