<?php

namespace App\Tests\Unit\Service;

use App\Service\GeocodingService;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class GeocodingServiceTest extends TestCase
{
    private $httpClient;
    private $response;
    private GeocodingService $geocodingService;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->response = $this->createMock(ResponseInterface::class);
        $this->geocodingService = new GeocodingService($this->httpClient);
    }

    public function testGeocodeAddress(): void
    {
        // Test successful geocoding
        $address = 'Paris, France';
        $mockResponseData = [
            [
                'lat' => '48.8566',
                'lon' => '2.3522',
                'display_name' => 'Paris, Île-de-France, France'
            ]
        ];

        $this->response->expects($this->once())
            ->method('toArray')
            ->willReturn($mockResponseData);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'https://nominatim.openstreetmap.org/search',
                $this->callback(function($options) use ($address) {
                    return isset($options['query']['q']) 
                        && $options['query']['q'] === $address
                        && $options['query']['format'] === 'json'
                        && $options['query']['limit'] === 1
                        && isset($options['headers']['User-Agent']);
                })
            )
            ->willReturn($this->response);

        $result = $this->geocodingService->geocodeAddress($address);

        $this->assertIsArray($result);
        $this->assertEquals(48.8566, $result['latitude']);
        $this->assertEquals(2.3522, $result['longitude']);
        $this->assertEquals('Paris, Île-de-France, France', $result['display_name']);
    }

    public function testGeocodeAddressReturnsNullWhenNoResults(): void
    {
        // Test geocoding with no results
        $address = 'NonExistentPlace12345';
        $emptyResponseData = [];

        $this->response->expects($this->once())
            ->method('toArray')
            ->willReturn($emptyResponseData);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->willReturn($this->response);

        $result = $this->geocodingService->geocodeAddress($address);

        $this->assertNull($result);
    }

    public function testCalculateDistance(): void
    {
        // Test the distance calculation using the Haversine formula
        // Boston coordinates
        $lat1 = 42.3601;
        $lon1 = -71.0589;
        
        // New York coordinates
        $lat2 = 40.7128;
        $lon2 = -74.0060;
        
        // The distance should be approximately 306 km
        $distance = $this->geocodingService->calculateDistance($lat1, $lon1, $lat2, $lon2);
        
        // Allow for a small margin of error due to rounding
        $this->assertEqualsWithDelta(306, $distance, 5);
        
        // Test with zero distance (same point)
        $samePointDistance = $this->geocodingService->calculateDistance($lat1, $lon1, $lat1, $lon1);
        $this->assertEqualsWithDelta(0, $samePointDistance, 0.1);
    }

    public function testReverseGeocode(): void
    {
        // Test reverse geocoding
        $latitude = 48.8566;
        $longitude = 2.3522;
        
        $mockResponseData = [
            'display_name' => 'Paris, Île-de-France, France',
            'address' => [
                'city' => 'Paris',
                'country' => 'France',
                'country_code' => 'fr'
            ]
        ];

        $this->response->expects($this->once())
            ->method('toArray')
            ->willReturn($mockResponseData);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'https://nominatim.openstreetmap.org/reverse',
                $this->callback(function($options) use ($latitude, $longitude) {
                    return isset($options['query']['lat']) 
                        && $options['query']['lat'] === $latitude
                        && isset($options['query']['lon'])
                        && $options['query']['lon'] === $longitude
                        && $options['query']['format'] === 'json'
                        && isset($options['headers']['User-Agent']);
                })
            )
            ->willReturn($this->response);

        $result = $this->geocodingService->reverseGeocode($latitude, $longitude);

        $this->assertIsArray($result);
        $this->assertEquals('Paris, Île-de-France, France', $result['display_name']);
        $this->assertArrayHasKey('address', $result);
        $this->assertEquals('Paris', $result['address']['city']);
        $this->assertEquals('France', $result['address']['country']);
    }

    public function testReverseGeocodeReturnsNullWhenNoAddressData(): void
    {
        // Test reverse geocoding with no address data
        $latitude = 0;
        $longitude = 0;
        
        $mockResponseData = [
            'display_name' => 'Middle of Nowhere'
            // No address field
        ];

        $this->response->expects($this->once())
            ->method('toArray')
            ->willReturn($mockResponseData);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->willReturn($this->response);

        $result = $this->geocodingService->reverseGeocode($latitude, $longitude);

        $this->assertNull($result);
    }
} 