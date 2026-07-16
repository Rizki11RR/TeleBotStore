<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    /** @var list<string> */
    protected $fillable = ['key', 'value', 'group'];

    /** Durasi cache setting dalam detik */
    private const CACHE_TTL = 3600;

    /*
    |--------------------------------------------------------------------------
    | Static Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Ambil nilai setting berdasarkan key.
     * Gunakan cache untuk mengurangi query.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting.{$key}", self::CACHE_TTL, function () use ($key, $default) {
            return static::where('key', $key)->value('value') ?? $default;
        });
    }

    /**
     * Set nilai setting dan invalidate cache.
     */
    public static function set(string $key, mixed $value, string $group = 'general'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );

        Cache::forget("setting.{$key}");
    }

    /**
     * Ambil semua setting dalam satu group.
     *
     * @return array<string, string|null>
     */
    public static function group(string $group): array
    {
        return static::where('group', $group)
            ->pluck('value', 'key')
            ->toArray();
    }
}
