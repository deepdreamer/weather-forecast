<?php

namespace App\Services;

use App\DTOs\DailyForecast;
use App\Enums\City;
use App\Exceptions\WeatherApiException;
use App\ValueObjects\WeatherForecast;
use CodeIgniter\HTTP\CURLRequest;
use Config\Services;

class WeatherService
{
    private CURLRequest $client;

    public function __construct()
    {
        $this->client = Services::curlrequest();
    }

    public function getWeatherForecastForCity(City $city): WeatherForecast
    {
        $data = $this->makeRequest($city);

        $days = [];
        foreach ($data['daily']['time'] as $index => $date) {
            $days[] = new DailyForecast(
                $date,
                (float) $data['daily']['temperature_2m_min'][$index],
                (float) $data['daily']['temperature_2m_max'][$index],
            );
        }

        return new WeatherForecast($city, $days);
    }

    /**
     * @return array{daily: array{time: array<int, string>, temperature_2m_max: array<int, float|int>, temperature_2m_min: array<int, float|int>}}
     */
    private function makeRequest(City $city): array
    {
        $coords = $city->getCoordinates();
        $timezone = $city->getTimezone();
        $forecastDays = 7;
        $dailySetting = 'temperature_2m_max,temperature_2m_min';
        $url = "https://api.open-meteo.com/v1/forecast?latitude={$coords->latitude}&longitude={$coords->longitude}&daily=$dailySetting&timezone=$timezone&forecast_days=$forecastDays";

        $apiResponse = $this->client->get($url);

        if ($apiResponse->getStatusCode() !== 200) {
            throw new WeatherApiException('API error: HTTP ' . $apiResponse->getStatusCode());
        }

        $body = $apiResponse->getBody();
        $data = json_decode($body ?? '', true);

        if (!is_array($data)) {
            throw new WeatherApiException('API error: Invalid JSON');
        }

        /** @var array<string, mixed> $data */
        if ($this->isErrorResponse($data)) {
            /** @var array{error: bool, reason: string} $data */
            throw new WeatherApiException('API error: ' . $data['reason']);
        }

        if (!$this->isValidForecastResponse($data)) {
            throw new WeatherApiException('Unexpected response shape from weather API.');
        }

        /** @var array{daily: array{time: array<int, string>, temperature_2m_max: array<int, float|int>, temperature_2m_min: array<int, float|int>}} $data */
        return $data;
    }

    /** @param array<string, mixed> $data */
    private function isErrorResponse(array $data): bool
    {
        if (isset($data['error']) && $data['error'] === true) {
            return true;
        }

        return false;
    }

    /** @param array<string, mixed>  $data */
    private function isValidForecastResponse(array $data): bool
    {
        if (!isset($data['daily']) || !is_array($data['daily'])) {
            return false;
        }

        $daily = $data['daily'];

        return isset($daily['time'], $daily['temperature_2m_max'], $daily['temperature_2m_min'])
            && is_array($daily['time'])
            && is_array($daily['temperature_2m_max'])
            && is_array($daily['temperature_2m_min']);
    }
}