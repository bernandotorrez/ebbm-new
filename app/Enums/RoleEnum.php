<?php

namespace App\Enums;

enum RoleEnum: string
{
    case Admin = 'admin';
    case Kanpus = 'kanpus';
    case Kanwil = 'kanwil';
    case Crew = 'crew';

    public static function values(): array
    {
        return [
            self::Admin->value => 'Admin',
            self::Kanpus->value => 'Kantor Pusat',
            self::Kanwil->value => 'Kantor Wilayah',
            self::Crew->value => 'Crew',
        ];
    }
}
