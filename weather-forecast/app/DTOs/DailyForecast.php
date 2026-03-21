<?php

namespace App\DTOs;

readonly class DailyForecast
{
    public function __construct(
        public string $date,
        public float $minTemp,
        public float $maxTemp,
    ) {}
}