<?php

namespace App\DTOs;

readonly class Coordinates
{
    public function __construct(
        public string $name,
        public float $latitude,
        public float $longitude,
    ) {}
}
