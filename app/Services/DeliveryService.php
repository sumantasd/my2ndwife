<?php

namespace App\Services;

use App\Models\RestaurantSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeliveryService
{
    /**
     * Calculate straight-line distance using the Haversine formula (in kilometres).
     */
    public function calculateHaversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadiusKm = 6371.0;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadiusKm * $c, 2);
    }

    /**
     * Calculate road distance via Google Maps Distance Matrix / Routes API if configured.
     * Gracefully falls back to Haversine distance.
     */
    public function calculateDistance(float $customerLat, float $customerLon): array
    {
        $restaurantLat = (float) RestaurantSetting::get('restaurant_latitude', 22.5535);
        $restaurantLon = (float) RestaurantSetting::get('restaurant_longitude', 88.3533);
        $method = RestaurantSetting::get('distance_calculation_method', 'haversine');
        $apiKey = RestaurantSetting::get('google_maps_api_key');
        $googleRoutesEnabled = (bool) RestaurantSetting::get('google_maps_routes_enabled', false);

        // Fallback straight-line
        $haversineDistance = $this->calculateHaversineDistance($restaurantLat, $restaurantLon, $customerLat, $customerLon);

        if ($method === 'google_routes' && $googleRoutesEnabled && !empty($apiKey)) {
            try {
                $response = Http::timeout(3)->get('https://maps.googleapis.com/maps/api/distancematrix/json', [
                    'origins' => "{$restaurantLat},{$restaurantLon}",
                    'destinations' => "{$customerLat},{$customerLon}",
                    'mode' => 'driving',
                    'key' => $apiKey,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['rows'][0]['elements'][0]['distance']['value'])) {
                        $distanceMeters = $data['rows'][0]['elements'][0]['distance']['value'];
                        $roadDistanceKm = round($distanceMeters / 1000.0, 2);
                        return [
                            'distance_km' => $roadDistanceKm,
                            'method' => 'google_routes',
                            'is_road_distance' => true,
                        ];
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Google Distance Matrix request failed, falling back to Haversine: ' . $e->getMessage());
            }
        }

        return [
            'distance_km' => $haversineDistance,
            'method' => 'haversine',
            'is_road_distance' => false,
        ];
    }

    /**
     * Check if a delivery coordinate is within the serviceable radius.
     */
    public function checkServiceability(?float $customerLat, ?float $customerLon): array
    {
        $deliveryEnabled = (bool) RestaurantSetting::get('delivery_enabled', true);
        if (!$deliveryEnabled) {
            return [
                'serviceable' => false,
                'distance_km' => 0.0,
                'max_radius_km' => 0.0,
                'method' => 'none',
                'message' => RestaurantSetting::get('delivery_unavailable_message', 'Sorry, home delivery is currently paused.'),
            ];
        }

        $maxRadiusKm = (float) RestaurantSetting::get('delivery_radius_km', 5.0);

        // If no coordinates provided (manual address without GPS)
        if ($customerLat === null || $customerLon === null || ($customerLat == 0.0 && $customerLon == 0.0)) {
            return [
                'serviceable' => true,
                'distance_km' => null,
                'max_radius_km' => $maxRadiusKm,
                'method' => 'manual_review',
                'message' => 'Delivery location will be manually verified by restaurant dispatch.',
            ];
        }

        $calc = $this->calculateDistance($customerLat, $customerLon);
        $distanceKm = $calc['distance_km'];

        if ($distanceKm > $maxRadiusKm) {
            $unavailableMsg = RestaurantSetting::get('delivery_unavailable_message', 'We do not deliver to this location yet.');
            return [
                'serviceable' => false,
                'distance_km' => $distanceKm,
                'max_radius_km' => $maxRadiusKm,
                'method' => $calc['method'],
                'message' => "{$unavailableMsg} Your location is {$distanceKm} km away (maximum delivery radius is {$maxRadiusKm} km). Takeaway is available!",
            ];
        }

        return [
            'serviceable' => true,
            'distance_km' => $distanceKm,
            'max_radius_km' => $maxRadiusKm,
            'method' => $calc['method'],
            'message' => "Location is serviceable ({$distanceKm} km away).",
        ];
    }

    /**
     * Calculate delivery fee based on configured calculation method.
     */
    public function calculateDeliveryFee(float $subtotal, ?float $distanceKm = null): float
    {
        $deliveryEnabled = (bool) RestaurantSetting::get('delivery_enabled', true);
        if (!$deliveryEnabled) {
            return 0.00;
        }

        $freeThreshold = (float) RestaurantSetting::get('free_delivery_threshold', 499.00);
        if ($freeThreshold > 0 && $subtotal >= $freeThreshold) {
            return 0.00;
        }

        $baseCharge = (float) RestaurantSetting::get('default_delivery_charge', 30.00);
        $method = RestaurantSetting::get('delivery_fee_calculation_method', 'fixed');

        if ($method === 'fixed' || $distanceKm === null || $distanceKm <= 0) {
            return $baseCharge;
        }

        if ($method === 'per_km') {
            $perKmRate = (float) RestaurantSetting::get('delivery_charge_per_km', 10.00);
            return round($baseCharge + ($distanceKm * $perKmRate), 2);
        }

        if ($method === 'distance_tiered') {
            $baseRadius = (float) RestaurantSetting::get('delivery_tiered_base_km', 3.0);
            $tierRate = (float) RestaurantSetting::get('delivery_tiered_extra_per_km', 12.00);
            if ($distanceKm <= $baseRadius) {
                return $baseCharge;
            }
            $extraKm = $distanceKm - $baseRadius;
            return round($baseCharge + ($extraKm * $tierRate), 2);
        }

        return $baseCharge;
    }

    /**
     * Check if the restaurant is open right now based on opening hours and status.
     */
    public function checkRestaurantOpenStatus(): array
    {
        // 1. Check temporary closed toggle
        $tempClosed = (bool) RestaurantSetting::get('is_temporarily_closed', false);
        if ($tempClosed) {
            return [
                'is_open' => false,
                'can_order' => false,
                'message' => RestaurantSetting::get('temporary_closed_notice', 'Restaurant is temporarily closed. We will be back soon!'),
            ];
        }

        // 2. Check general orders accepted toggle
        $acceptingOrders = (bool) RestaurantSetting::get('is_restaurant_open', true);
        if (!$acceptingOrders) {
            return [
                'is_open' => false,
                'can_order' => false,
                'message' => 'Kitchen is currently not accepting new orders.',
            ];
        }

        // 3. Day of week & hours check
        $timezone = RestaurantSetting::get('restaurant_timezone', 'Asia/Kolkata');
        $now = Carbon::now($timezone);
        $dayName = strtolower($now->format('l')); // monday, tuesday, ...

        $hoursSetting = RestaurantSetting::get('opening_hours_json', null);
        if (is_string($hoursSetting)) {
            $hoursSetting = json_decode($hoursSetting, true);
        }

        if (is_array($hoursSetting) && isset($hoursSetting[$dayName])) {
            $dayConfig = $hoursSetting[$dayName];
            $isOpenDay = (bool) ($dayConfig['is_open'] ?? true);
            if (!$isOpenDay) {
                return [
                    'is_open' => false,
                    'can_order' => false,
                    'message' => "Restaurant is closed on " . ucfirst($dayName) . "s.",
                ];
            }

            $openTime = $dayConfig['open_time'] ?? '11:00';
            $closeTime = $dayConfig['close_time'] ?? '23:30';

            $openDt = Carbon::createFromTimeString($openTime, $timezone);
            $closeDt = Carbon::createFromTimeString($closeTime, $timezone);

            // Handle midnight-crossing hours (e.g. 18:00 to 02:00)
            if ($closeDt->lessThanOrEqualTo($openDt)) {
                $closeDt->addDay();
            }

            if ($now->lessThan($openDt) || $now->greaterThan($closeDt)) {
                return [
                    'is_open' => false,
                    'can_order' => false,
                    'message' => "Kitchen is closed. Opening hours today: {$openTime} - {$closeTime}.",
                ];
            }
        }

        return [
            'is_open' => true,
            'can_order' => true,
            'message' => 'Restaurant is open and accepting orders.',
        ];
    }

    /**
     * Reverse geocode GPS coordinates into a readable postal address.
     * Tries Google Maps Geocoding API if key configured, then OpenStreetMap Nominatim with safe fallback.
     */
    public function reverseGeocode(?float $lat, ?float $lon): array
    {
        if ($lat === null || $lon === null || ($lat == 0.0 && $lon == 0.0)) {
            return [
                'success' => false,
                'message' => 'No coordinates provided for reverse geocoding.',
            ];
        }

        // Validate coordinate bounds
        if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
            return [
                'success' => false,
                'message' => 'Coordinates out of valid geographical range.',
            ];
        }

        $apiKey = RestaurantSetting::get('google_maps_api_key');
        $googleEnabled = (bool) RestaurantSetting::get('google_maps_js_enabled', false);

        // 1. Try Google Maps Geocoding if configured
        if ($googleEnabled && !empty($apiKey)) {
            try {
                $response = Http::timeout(4)->get('https://maps.googleapis.com/maps/api/geocode/json', [
                    'latlng' => "{$lat},{$lon}",
                    'key' => $apiKey,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (!empty($data['results'][0])) {
                        $firstResult = $data['results'][0];
                        $formatted = $firstResult['formatted_address'] ?? '';

                        $street = '';
                        $city = '';
                        $postalCode = '';

                        foreach ($firstResult['address_components'] ?? [] as $component) {
                            $types = $component['types'] ?? [];
                            if (in_array('street_number', $types) || in_array('route', $types) || in_array('sublocality', $types) || in_array('neighborhood', $types)) {
                                $street .= ($street ? ', ' : '') . $component['long_name'];
                            }
                            if (in_array('locality', $types)) {
                                $city = $component['long_name'];
                            }
                            if (in_array('postal_code', $types)) {
                                $postalCode = $component['long_name'];
                            }
                        }

                        return [
                            'success' => true,
                            'latitude' => $lat,
                            'longitude' => $lon,
                            'formatted_address' => $formatted,
                            'address_line_1' => $street ?: $formatted,
                            'city' => $city ?: RestaurantSetting::get('restaurant_city', 'Kolkata'),
                            'postal_code' => $postalCode,
                            'provider' => 'google_maps',
                        ];
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Google Geocoding failed, falling back to Nominatim: ' . $e->getMessage());
            }
        }

        // 2. OpenStreetMap Nominatim reverse geocoding fallback (no API key required)
        try {
            $response = Http::timeout(4)
                ->withHeaders([
                    'User-Agent' => 'My2ndWifeFoodDelivery/1.0 (contact@my2ndwife.test)',
                    'Accept-Language' => 'en',
                ])
                ->get('https://nominatim.openstreetmap.org/reverse', [
                    'format' => 'jsonv2',
                    'lat' => $lat,
                    'lon' => $lon,
                    'zoom' => 18,
                    'addressdetails' => 1,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $addr = $data['address'] ?? [];
                $displayName = $data['display_name'] ?? '';

                $streetParts = array_filter([
                    $addr['amenity'] ?? null,
                    $addr['building'] ?? null,
                    $addr['house_number'] ?? null,
                    $addr['road'] ?? null,
                    $addr['neighbourhood'] ?? null,
                    $addr['suburb'] ?? null,
                ]);

                $street = !empty($streetParts) ? implode(', ', $streetParts) : ($addr['residential'] ?? $displayName);
                $city = $addr['city'] ?? $addr['town'] ?? $addr['village'] ?? $addr['county'] ?? RestaurantSetting::get('restaurant_city', 'Kolkata');
                $postalCode = $addr['postcode'] ?? '';

                return [
                    'success' => true,
                    'latitude' => $lat,
                    'longitude' => $lon,
                    'formatted_address' => $displayName,
                    'address_line_1' => $street ?: $displayName,
                    'city' => $city,
                    'postal_code' => $postalCode,
                    'provider' => 'openstreetmap',
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('OpenStreetMap reverse geocoding failed: ' . $e->getMessage());
        }

        return [
            'success' => false,
            'message' => 'Reverse geocoding service currently unavailable. Please enter address manually.',
            'latitude' => $lat,
            'longitude' => $lon,
        ];
    }
}
