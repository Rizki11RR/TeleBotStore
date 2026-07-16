<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramUser extends Model
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'telegram_id',
        'username',
        'first_name',
        'last_name',
        'is_blocked',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'telegram_id' => 'integer',
        'is_blocked'  => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function session()
    {
        return $this->hasOne(TelegramSession::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /** Nama lengkap user Telegram */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /** Mention Telegram (@username atau nama jika tidak punya username) */
    public function getMentionAttribute(): string
    {
        return $this->username ? "@{$this->username}" : $this->full_name;
    }
}
