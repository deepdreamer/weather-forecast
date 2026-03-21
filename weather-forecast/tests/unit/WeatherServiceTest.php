<?php

namespace unit;

use App\Enums\City;
use App\Exceptions\WeatherApiException;
use App\Services\WeatherService;
use CodeIgniter\HTTP\CURLRequest;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;


final class WeatherServiceTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        Services::reset();
    }

    public function testThrowsOnNonSuccessfulHttpStatus(): void
    {
        $this->injectMockResponse(500, null);

        $this->expectException(WeatherApiException::class);
        $this->expectExceptionMessage('API error: HTTP 500');

        (new WeatherService())->getWeatherForecastForCity(City::PRAHA);
    }

    public function testThrowsOnInvalidJson(): void
    {
        $this->injectMockResponse(200, null);

        $this->expectException(WeatherApiException::class);
        $this->expectExceptionMessage('API error: Invalid JSON');

        (new WeatherService())->getWeatherForecastForCity(City::PRAHA);

        $this->injectMockResponse(200, '');

        $this->expectException(WeatherApiException::class);
        $this->expectExceptionMessage('API error: Invalid JSON');

        (new WeatherService())->getWeatherForecastForCity(City::PRAHA);
    }

    public function testThrowsOnErrorByThirdParty(): void
    {
        $this->injectMockResponse(200, <<<'JSON'
        {
          "error": true,
          "reason": "Cannot initialize WeatherVariable from invalid String value tempeture_2m for key hourly"
        }
        JSON
        );


        $this->expectException(WeatherApiException::class);
        $this->expectExceptionMessage('API error: Cannot initialize WeatherVariable from invalid String value tempeture_2m for key hourly');

        (new WeatherService())->getWeatherForecastForCity(City::PRAHA);
    }

    private function injectMockResponse(int $statusCode, ?string $body): void
    {
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getStatusCode')->willReturn($statusCode);
        $mockResponse->method('getBody')->willReturn($body);

        $mockClient = $this->createMock(CURLRequest::class);
        $mockClient->method('get')->willReturn($mockResponse);

        Services::injectMock('curlrequest', $mockClient);
    }
}