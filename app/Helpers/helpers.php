<?php

declare(strict_types=1);

use Illuminate\Support\Str;

if (! function_exists('human_bytes')) {
    /**
     * Convert a byte count into a human-readable string (e.g. "1.5 GB").
     *
     * Useful for displaying data usage in ISP billing dashboards.
     */
    function human_bytes(int|float $bytes, int $precision = 2): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $power = (int) floor(log($bytes, 1024));
        $power = min($power, count($units) - 1);

        return sprintf('%.'.$precision.'f %s', $bytes / (1024 ** $power), $units[$power]);
    }
}

if (! function_exists('mac_normalize')) {
    /**
     * Normalize a MAC address to colon-separated upper-case form.
     *
     * MikroTik and FreeRADIUS occasionally return hyphenated or lower-case
     * MAC addresses; this helper canonicalizes them for consistent storage.
     */
    function mac_normalize(string $mac): string
    {
        return strtoupper(preg_replace('/[^a-f0-9]/i', ':', trim($mac)));
    }
}

if (! function_exists('random_voucher_code')) {
    /**
     * Generate a human-friendly voucher code suitable for printed tickets.
     *
     * Avoids ambiguous characters (0/O, 1/I/L) to reduce support calls.
     */
    function random_voucher_code(int $length = 8): string
    {
        $alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

        return Str::upper(Str::substr(
            str_shuffle(str_repeat($alphabet, (int) ceil($length / strlen($alphabet)))),
            0,
            $length
        ));
    }
}
