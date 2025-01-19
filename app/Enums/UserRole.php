<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Manager = 'manager';
    case User = 'user';

    public static function default(): static
    {
        return self::User;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
