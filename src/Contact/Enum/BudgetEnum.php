<?php

namespace App\Contact\Enum;

enum BudgetEnum: string
{
    case P1 = 'p1';
    case P2 = 'p2';
    case P3 = 'p3';
    case P4 = 'p4';
    case unknow = 'unknow';

    public function label(): string
    {
        return match ($this) {
            self::P1 => 'Moins de 3 000€',
            self::P2 => 'Entre 3 000€ et 10 000€',
            self::P3 => 'Entre 10 000€ et 30 000€',
            self::P4 => 'Plus de 30 000€',
            self::unknow => 'Budget non défini',
        };
    }
}
