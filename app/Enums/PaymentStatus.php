<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case UNPAID   = 'UNPAID';
    case WAITING  = 'WAITING';
    case VERIFIED = 'VERIFIED';
    case REJECTED = 'REJECTED';

    /** Tampilan label di UI */
    public function label(): string
    {
        return match ($this) {
            self::UNPAID   => 'Belum Bayar',
            self::WAITING  => 'Menunggu Verifikasi',
            self::VERIFIED => 'Terverifikasi',
            self::REJECTED => 'Ditolak',
        };
    }

    /** Bootstrap badge class */
    public function badgeClass(): string
    {
        return match ($this) {
            self::UNPAID   => 'badge bg-secondary',
            self::WAITING  => 'badge bg-warning text-dark',
            self::VERIFIED => 'badge bg-success',
            self::REJECTED => 'badge bg-danger',
        };
    }
}
