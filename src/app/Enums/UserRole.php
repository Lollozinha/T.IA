<?php

namespace App\Enums;

enum UserRole: string
{
    case Responsavel = 'responsavel';
    case Mediador = 'mediador';

    public function label(): string
    {
        return match ($this) {
            self::Responsavel => 'Responsável',
            self::Mediador => 'Mediador',
        };
    }

    public function requiresTwoFactor(): bool
    {
        return $this === self::Mediador;
    }
}
