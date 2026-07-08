<?php
declare(strict_types=1);

namespace App\lib;

final class Validator
{
    public static function required(string $value, string $field): void
    {
        if (trim($value) === '') {
            throw new \InvalidArgumentException($field . ' is required');
        }
    }

    public static function email(string $value, string $field): void
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException($field . ' must be a valid email');
        }
    }

    public static function intRange($value, int $min, int $max, string $field): void
    {
        $v = (int)$value;
        if ($v < $min || $v > $max) {
            throw new \InvalidArgumentException($field . ' out of range');
        }
    }

    public static function password(string $value, string $field = 'Password'): void
    {
        $length = strlen($value);
        if ($length < 8) {
            throw new \InvalidArgumentException($field . ' must be at least 8 characters long');
        }
        if (!preg_match('/[A-Za-z]/', $value) || !preg_match('/[0-9]/', $value)) {
            throw new \InvalidArgumentException($field . ' must contain both letters and numbers');
        }
    }
}

