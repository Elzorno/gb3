<?php

declare(strict_types=1);

namespace App\Domain\Auth;

use Illuminate\Contracts\Session\Session;

class PinRateLimiter
{
    public function __construct(
        private readonly Session $session,
        private readonly int $windowSec = 300,
        private readonly int $maxAttempts = 5,
        private readonly int $lockSec = 600,
    ) {
    }

    public function remaining(string $key): int
    {
        $row = $this->row($key);
        if ($row === null) {
            return 0;
        }

        $remaining = ($row['lock_until'] ?? 0) - time();
        if ($remaining <= 0) {
            $this->forget($key);
            return 0;
        }

        return $remaining;
    }

    public function recordFailure(string $key): int
    {
        $now = time();
        $row = $this->row($key) ?? [
            'window_started' => $now,
            'attempts' => 0,
            'lock_until' => 0,
        ];

        if (($row['lock_until'] ?? 0) > $now) {
            return (int)$row['lock_until'] - $now;
        }

        if (($now - (int)$row['window_started']) > $this->windowSec) {
            $row['window_started'] = $now;
            $row['attempts'] = 0;
            $row['lock_until'] = 0;
        }

        $row['attempts'] = (int)$row['attempts'] + 1;
        if ((int)$row['attempts'] >= $this->maxAttempts) {
            $row['lock_until'] = $now + $this->lockSec;
            $this->put($key, $row);
            return $this->lockSec;
        }

        $this->put($key, $row);
        return 0;
    }

    public function clear(string $key): void
    {
        $this->forget($key);
    }

    private function row(string $key): ?array
    {
        $all = $this->session->get('gb2_pin_rate', []);
        if (!is_array($all) || !isset($all[$key]) || !is_array($all[$key])) {
            return null;
        }

        return $all[$key];
    }

    private function put(string $key, array $row): void
    {
        $all = $this->session->get('gb2_pin_rate', []);
        if (!is_array($all)) {
            $all = [];
        }

        $all[$key] = [
            'window_started' => (int)($row['window_started'] ?? time()),
            'attempts' => (int)($row['attempts'] ?? 0),
            'lock_until' => (int)($row['lock_until'] ?? 0),
        ];

        $this->session->put('gb2_pin_rate', $all);
    }

    private function forget(string $key): void
    {
        $all = $this->session->get('gb2_pin_rate', []);
        if (!is_array($all) || !array_key_exists($key, $all)) {
            return;
        }

        unset($all[$key]);
        $this->session->put('gb2_pin_rate', $all);
    }
}
