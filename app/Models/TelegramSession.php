<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramSession extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'telegram_user_id',
        'state',
        'data',
        'expires_at',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'data'       => 'array',
        'expires_at' => 'datetime',
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

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /** Cek apakah sesi sudah expired */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /** Ambil nilai dari data sesi */
    public function get(string $key, mixed $default = null): mixed
    {
        return data_get($this->data, $key, $default);
    }

    /** Set nilai ke data sesi */
    public function set(string $key, mixed $value): void
    {
        $data = $this->data ?? [];
        data_set($data, $key, $value);
        $this->update(['data' => $data]);
    }
}
