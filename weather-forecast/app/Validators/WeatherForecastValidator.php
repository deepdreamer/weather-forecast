<?php

namespace App\Validators;

use App\Validators\Exception\InvalidRequest;
use App\DTOs\WeatherForecastRequest;

class WeatherForecastValidator
{
    public function validate(Object $jsonRequest): WeatherForecastRequest
    {
        if (!isset($jsonRequest->city)) {
            throw new InvalidRequest('Field city is required');
        }

        return new WeatherForecastRequest(mb_strtolower($jsonRequest->city));
    }
}