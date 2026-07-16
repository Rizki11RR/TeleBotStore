<?php

namespace App\Enums;

enum DeliveryType: string
{
    case FILE   = 'file';
    case TEXT   = 'text';
    case MANUAL = 'manual';

    /** Tampilan label di UI */
    public function label(): string
    {
        return match ($this) {
            self::FILE   => 'File',
            self::TEXT   => 'Teks Otomatis',
            self::MANUAL => 'Manual',
        };
    }

    /** Bootstrap icon class */
    public function icon(): string
    {
        return match ($this) {
            self::FILE   => 'bi bi-file-earmark-arrow-down',
            self::TEXT   => 'bi bi-chat-text',
            self::MANUAL => 'bi bi-person-gear',
        };
    }
}
