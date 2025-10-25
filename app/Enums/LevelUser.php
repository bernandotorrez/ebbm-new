<?php

namespace App\Enums;

enum LevelUser: string
{
    case ADMIN = 'admin';
    case KANPUS = 'kanpus';
    case KANWIL = 'kanwil';
    case CREW = 'crew';

    public static function values(): array
    {
        return [
            self::ADMIN->value => 'Admin',
            self::KANPUS->value => 'Kantor Pusat',
            self::KANWIL->value => 'Kantor Wilayah',
            self::CREW->value => 'Crew',
        ];
    }

    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Admin',
            self::KANPUS => 'Kantor Pusat',
            self::KANWIL => 'Kantor Wilayah',
            self::CREW => 'Crew',
        };
    }
}
