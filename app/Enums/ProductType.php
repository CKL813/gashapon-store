<?php

namespace App\Enums;

enum ProductType: string
{
    case Specific = 'specific';
    case Random   = 'random';

    public function label(): string
    {
        return match ($this) {
            self::Specific => 'Specific',
            self::Random   => 'Random Capsule',
        };
    }
}
