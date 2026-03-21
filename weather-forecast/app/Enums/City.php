<?php

namespace App\Enums;

use App\DTOs\Coordinates;

enum City: string
{
    case PRAHA = 'praha';
    case BRNO = 'brno';
    case OSTRAVA = 'ostrava';
    case OLOMOUC = 'olomouc';
    case PLZEN = 'plzeň';
    case PARDUBICE = 'pardubice';

    public function getTimezone(): string
    {
        return match($this) {
            self::PRAHA,
            self::BRNO,
            self::OSTRAVA,
            self::OLOMOUC,
            self::PLZEN,
            self::PARDUBICE => 'Europe/Prague',
        };
    }

    public function getCoordinates(): Coordinates
    {
        return match($this) {
            self::PRAHA => new Coordinates('Praha', 50.085011, 14.426517),
            self::BRNO => new Coordinates('Brno', 49.192244, 16.611338),
            self::OSTRAVA => new Coordinates('Ostrava', 49.834914, 18.282008),
            self::OLOMOUC => new Coordinates('Olomouc', 49.594057, 17.251143),
            self::PLZEN => new Coordinates('Plzeň', 49.747742, 13.377525),
            self::PARDUBICE => new Coordinates('Pardubice', 50.038581, 15.779136),
        };
    }
}