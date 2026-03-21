<?php

namespace Tests\Unit;

use CodeIgniter\HTTP\CURLRequest;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

final class WeatherControllerTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function tearDown(): void
    {
        parent::tearDown();
        Services::reset();
    }

    public function testForecastReturnsWeatherForValidCity(): void
    {
        $this->injectMockResponse(200, '{"latitude":49.84,"longitude":18.279999,"generationtime_ms":0.10645389556884766,"utc_offset_seconds":3600,"timezone":"Europe/Prague","timezone_abbreviation":"GMT+1","elevation":217.0,"daily_units":{"time":"iso8601","temperature_2m_max":"°C","temperature_2m_min":"°C"},"daily":{"time":["2026-03-21","2026-03-22","2026-03-23","2026-03-24","2026-03-25","2026-03-26","2026-03-27"],"temperature_2m_max":[11.5,13.3,14.8,14.7,16.8,9.0,4.4],"temperature_2m_min":[4.5,0.5,2.3,3.8,1.7,2.8,2.0]}}');

        $result = $this->withBodyFormat('json')->post('api/weather', ['city' => 'Ostrava']);

        $result->assertStatus(200);
        $result->assertJSONFragment(['city' => 'Ostrava']);
        $result->assertJSONFragment([
            'temperature' => [
                ['date' => '2026-03-21', 'min' => 4.5, 'max' => 11.5],
                ['date' => '2026-03-22', 'min' => 0.5, 'max' => 13.3],
            ],
        ]);
    }

    private function injectMockResponse(int $statusCode, string $body): void
    {
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getStatusCode')->willReturn($statusCode);
        $mockResponse->method('getBody')->willReturn($body);

        $mockClient = $this->createMock(CURLRequest::class);
        $mockClient->method('get')->willReturn($mockResponse);

        Services::injectMock('curlrequest', $mockClient);
    }
}