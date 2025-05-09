<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeocodingService
{
    private $httpClient;
    private $nominatimUrl = 'https://nominatim.openstreetmap.org/search';
    
    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }
    
    /**
     * Geocode an address string to latitude and longitude coordinates
     */
    public function geocodeAddress(string $address): ?array
    {
        $response = $this->httpClient->request('GET', $this->nominatimUrl, [
            'query' => [
                'q' => $address,
                'format' => 'json',
                'limit' => 1
            ],
            'headers' => [
                'User-Agent' => 'SkillSwap/1.0'
            ]
        ]);
        
        $data = $response->toArray();
        
        if (empty($data)) {
            return null;
        }
        
        return [
            'latitude' => (float) $data[0]['lat'],
            'longitude' => (float) $data[0]['lon'],
            'display_name' => $data[0]['display_name'] ?? $address
        ];
    }
    
    /**
     * Calculate the distance between two points in kilometers
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        // Haversine formula
        $earthRadius = 6371; // km
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c;
    }
    
    /**
     * Get address details from coordinates
     */
    public function reverseGeocode(float $latitude, float $longitude): ?array
    {
        $response = $this->httpClient->request('GET', 'https://nominatim.openstreetmap.org/reverse', [
            'query' => [
                'lat' => $latitude,
                'lon' => $longitude,
                'format' => 'json'
            ],
            'headers' => [
                'User-Agent' => 'SkillSwap/1.0'
            ]
        ]);
        
        $data = $response->toArray();
        
        if (!isset($data['address'])) {
            return null;
        }
        
        return [
            'display_name' => $data['display_name'] ?? null,
            'address' => $data['address']
        ];
    }
} 