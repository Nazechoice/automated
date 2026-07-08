<?php
declare(strict_types=1);

namespace App\lib;

final class Security
{
    public static function sanitize(?string $s): string
    {
        return trim((string)$s);
    }
}

