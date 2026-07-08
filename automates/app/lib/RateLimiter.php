<?php
declare(strict_types=1);

namespace App\lib;

final class RateLimiter
{
    public static function allow(string $key, int $limit, int $windowSeconds): bool
    {
        $now = time();
        $bucket = $_SESSION['rl'][$key] ?? ['count' => 0, 'start' => $now];

        if (($now - (int)$bucket['start']) >= $windowSeconds) {
            $bucket = ['count' => 0, 'start' => $now];
        }

        $bucket['count']++;
        $_SESSION['rl'][$key] = $bucket;

        return $bucket['count'] <= $limit;
    }
}

