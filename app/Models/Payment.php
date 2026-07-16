<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'order_id',
        'amount',
        'status',
        'verified_at',
        'verified_by',
        'rejection_reason',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'status'      => PaymentStatus::class,
        'amount'      => 'decimal:2',
        'verified_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function order()
    {
        return $this->belongsTo(Order::class)->withTrashed();
    }

    public function verifier()
    {
        return $this->belongsTo(Admin::class, 'verified_by');
    }

    public function proofs()
    {
        return $this->hasMany(PaymentProof::class);
    }

    public function latestProof()
    {
        return $this->hasOne(PaymentProof::class)->latestOfMany();
    }
}
