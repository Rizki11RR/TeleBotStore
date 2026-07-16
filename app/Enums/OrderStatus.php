<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'PENDING';
    case WAITING_PAYMENT = 'WAITING_PAYMENT';
    case WAITING_VERIFICATION = 'WAITING_VERIFICATION';
    case PAID = 'PAID';
    case COMPLETED = 'COMPLETED';
    case CANCELLED = 'CANCELLED';

    /** Tampilan label di UI */
    public function label(): string
    {
        return match ($this) {
            self::PENDING             => 'Pending',
            self::WAITING_PAYMENT     => 'Menunggu Pembayaran',
            self::WAITING_VERIFICATION => 'Menunggu Verifikasi',
            self::PAID                => 'Dibayar',
            self::COMPLETED           => 'Selesai',
            self::CANCELLED           => 'Dibatalkan',
        };
    }

    /** Bootstrap badge class */
    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING             => 'badge bg-secondary',
            self::WAITING_PAYMENT     => 'badge bg-warning text-dark',
            self::WAITING_VERIFICATION => 'badge bg-info text-dark',
            self::PAID                => 'badge bg-primary',
            self::COMPLETED           => 'badge bg-success',
            self::CANCELLED           => 'badge bg-danger',
        };
    }
}
