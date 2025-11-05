<?php

namespace App\Enums;

enum LevelUser: string
{
    case ADMIN = 'admin';
    case KANPUS = 'kanpus';
    case KANSAR = 'kansar';
    case ABK = 'abk';

    public static function values(): array
    {
        return [
            self::ADMIN->value => 'Admin',
            self::KANPUS->value => 'Kantor Pusat',
            self::KANSAR->value => 'Kantor SAR',
            self::ABK->value => 'ABK',
        ];
    }

    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Admin',
            self::KANPUS => 'Kantor Pusat',
            self::KANSAR => 'Kantor SAR',
            self::ABK => 'ABK',
        };
    }
}
