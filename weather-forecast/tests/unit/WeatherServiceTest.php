<?php

namespace unit;

use App\Enums\City;
use App\Exceptions\WeatherApiException;
use App\Services\WeatherService;
use App\ValueObjects\WeatherForecast;
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
        $this->injectMockResponseWithBody(500, null);

        $this->expectException(WeatherApiException::class);
        $this->expectExceptionMessage('API error: HTTP 500');

        (new WeatherService())->getWeatherForecastForCity(City::PRAHA);
    }

    public function testThrowsOnInvalidJson(): void
    {
        $this->injectMockResponseWithBody(200, null);

        $this->expectException(WeatherApiException::class);
        $this->expectExceptionMessage('API error: Invalid JSON');

        (new WeatherService())->getWeatherForecastForCity(City::PRAHA);

        $this->injectMockResponseWithBody(200, '');

        $this->expectException(WeatherApiException::class);
        $this->expectExceptionMessage('API error: Invalid JSON');

        (new WeatherService())->getWeatherForecastForCity(City::PRAHA);
    }

    public function testThrowsOnErrorByThirdParty(): void
    {
        $this->injectMockResponseWithBody(200, <<<'JSON'
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

    public function testRetryOn5xx(): void
    {
        $validBody = '{"daily":{"time":["2026-04-16"],"temperature_2m_max":[15.0],"temperature_2m_min":[5.0]}}';

        $mockClient = $this->createMock(CURLRequest::class);
        $mockClient->expects($this->exactly(3))
            ->method('get')
            ->willReturnOnConsecutiveCalls(
                $this->givenMockResponse(500, null),
                $this->givenMockResponse(500, null),
                $this->givenMockResponse(200, $validBody),
            );

        Services::injectMock('curlrequest', $mockClient);

        $result = (new WeatherService())->getWeatherForecastForCity(City::PRAHA);
        $this->assertSame(City::PRAHA, $result->city);
    }

    public function testNetworkExceptionTriggersRetryAndRecovers(): void
    {
        $validBody = '{"daily":{"time":["2026-04-16"],"temperature_2m_max":[15.0],"temperature_2m_min":[5.0]}}';

        $mockClient = $this->createMock(CURLRequest::class);
        $mockClient->expects($this->exactly(2))
            ->method('get')
            ->willReturnOnConsecutiveCalls(
                $this->throwException(new \Exception('Connection timed out')),
                $this->givenMockResponse(200, $validBody),
            );

        Services::injectMock('curlrequest', $mockClient);

        $result = (new WeatherService())->getWeatherForecastForCity(City::PRAHA);
        $this->assertSame(City::PRAHA, $result->city);
    }

    public function testExhaustingRetries(): void
    {
        $mockClient = $this->createMock(CURLRequest::class);
        $mockClient->expects($this->exactly(3))
            ->method('get')
            ->willReturnOnConsecutiveCalls(
                $this->givenMockResponse(500, null),
                $this->givenMockResponse(500, null),
                $this->givenMockResponse(500, null),
            );

        Services::injectMock('curlrequest', $mockClient);

        $this->expectException(WeatherApiException::class);
        (new WeatherService())->getWeatherForecastForCity(City::PRAHA);
    }

    public function testNoRetriesFor4xx(): void
    {
        $mockClient = $this->createMock(CURLRequest::class);
        $mockClient->expects($this->exactly(1))
            ->method('get')
            ->willReturn($this->givenMockResponse(404, null));

        Services::injectMock('curlrequest', $mockClient);

        $this->expectException(WeatherApiException::class);
        (new WeatherService())->getWeatherForecastForCity(City::PRAHA);
    }

    private function injectMockResponseWithBody(int $statusCode, ?string $body): void
    {
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getStatusCode')->willReturn($statusCode);
        $mockResponse->method('getBody')->willReturn($body);

        $mockClient = $this->createMock(CURLRequest::class);
        $mockClient->method('get')->willReturn($mockResponse);

        Services::injectMock('curlrequest', $mockClient);
    }

    private function givenMockResponse(int $statusCode, ?string $body): ResponseInterface
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn($statusCode);
        $response->method('getBody')->willReturn($body);
        return $response;
    }
}