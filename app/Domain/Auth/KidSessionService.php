<?php

declare(strict_types=1);

namespace App\Domain\Auth;

use Illuminate\Http\Request;

class KidSessionService
{
    public function pinPolicyOk(string $pin, int $min = 6, int $max = 6): bool
    {
        if (!preg_match('/^[0-9]+$/', $pin)) {
            return false;
        }

        $len = strlen($pin);
        return $len >= $min && $len <= $max;
    }

    public function lockKey(Request $request, int $kidId): string
    {
        $ip = (string)$request->ip();
        $ua = (string)$request->userAgent();
        return hash('sha256', $kidId . '|' . $ip . '|' . $ua);
    }

    public function formatWait(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds . 's';
        }

        $m = intdiv($seconds, 60);
        $s = $seconds % 60;
        if ($m < 60) {
            return $s > 0 ? ($m . 'm ' . $s . 's') : ($m . 'm');
        }

        $h = intdiv($m, 60);
        $m2 = $m % 60;
        return $m2 > 0 ? ($h . 'h ' . $m2 . 'm') : ($h . 'h');
    }

    public function loginKid(Request $request, int $kidId): void
    {
        $request->session()->regenerate();
        $request->session()->put('gb2_kid_id', $kidId);
        $request->session()->put('gb2_kid_logged_in_at', now()->toIso8601String());
    }

    public function logoutKid(Request $request): void
    {
        $request->session()->forget(['gb2_kid_id', 'gb2_kid_logged_in_at', 'kid_name']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
