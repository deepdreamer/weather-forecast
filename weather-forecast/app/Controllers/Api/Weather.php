<?php

namespace App\Controllers\Api;

use App\Exceptions\WeatherApiException;
use App\Services\WeatherService;
use App\Validators\Exception\InvalidRequest;
use App\Validators\WeatherForecastValidator;
use CodeIgniter\Controller;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use App\Enums\City;

class Weather extends Controller
{
    use ResponseTrait;

    private WeatherForecastValidator $requestValidator;
    private WeatherService $weatherService;

    public function __construct()
    {
        $this->requestValidator = Services::weatherForecastValidator();
        $this->weatherService = Services::weatherService();
    }

    public function forecast(): ResponseInterface
    {
        assert($this->request instanceof IncomingRequest);
        $requestBody = $this->request->getJSON();

        if (!$requestBody instanceof \stdClass) {
            throw new InvalidRequest('Request body must be JSON.', 400);
        }

        $city = City::tryFrom(
            $this->requestValidator->validate($requestBody)->cityName
        ) ?? throw new InvalidRequest('Invalid city.', 404);
        try {
            $forecast = $this->weatherService->getWeatherForecastForCity($city);
        } catch (WeatherApiException $e) {
            log_message('error', '[WeatherApiException] {message}', ['message' => $e->getMessage()]);
            return $this->response->setStatusCode(503)->setJson([
                'error' => 'Weather data is currently unavailable.',
            ]);
        }

        return $this->response->setJson([
            'city' => ucfirst($city->value),
            'temperature' => $forecast->getFormattedForecast(),
        ]);
    }

}