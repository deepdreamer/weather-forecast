<?php

namespace App\DTOs;

readonly class WeatherForecastRequest
{
    public function __construct(
        public string $cityName,
    ) {}
}