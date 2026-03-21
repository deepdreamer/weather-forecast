<?php

namespace App\ValueObjects;

use App\DTOs\DailyForecast;
use App\Enums\City;

readonly class WeatherForecast
{
    public function __construct(
        public City  $city,

        /** @var array<DailyForecast> */
        public array $days,
    ) {}


    /**
     * @return array<int, array{date: string, min: float, max: float}>
     */
    public function getFormattedForecast(): array
    {
        return array_map(fn(DailyForecast $day) => [
            'date' => $day->date,
            'min' => $day->minTemp,
            'max' => $day->maxTemp
        ], $this->days);
    }
}