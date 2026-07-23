<?php

namespace App\Support;

class UserAgentParser
{
    /**
     * Ringkasan device yang mudah dibaca dari User-Agent, misal "Chrome di Windows".
     * Bukan parser lengkap — cukup untuk ditampilkan di daftar sesi login.
     */
    public static function label(?string $userAgent): string
    {
        if (! $userAgent) {
            return 'Perangkat tidak diketahui';
        }

        $os = match (true) {
            (bool) preg_match('/windows/i', $userAgent)              => 'Windows',
            (bool) preg_match('/iphone|ipad|ipod/i', $userAgent)     => 'iOS',
            (bool) preg_match('/mac os x|macintosh/i', $userAgent)   => 'macOS',
            (bool) preg_match('/android/i', $userAgent)              => 'Android',
            (bool) preg_match('/linux/i', $userAgent)                => 'Linux',
            default                                                  => null,
        };

        $browser = match (true) {
            (bool) preg_match('/edg\//i', $userAgent)     => 'Edge',
            (bool) preg_match('/chrome\//i', $userAgent)  => 'Chrome',
            (bool) preg_match('/crios\//i', $userAgent)   => 'Chrome',
            (bool) preg_match('/firefox\//i', $userAgent) => 'Firefox',
            (bool) preg_match('/fxios\//i', $userAgent)   => 'Firefox',
            (bool) preg_match('/safari\//i', $userAgent)  => 'Safari',
            default                                        => null,
        };

        if ($browser && $os) {
            return "{$browser} di {$os}";
        }

        return $browser ?? $os ?? 'Perangkat tidak diketahui';
    }
}
