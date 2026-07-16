<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentProof extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'payment_id',
        'file_path',
        'file_name',
        'telegram_file_id',
        'uploaded_at',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
