<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class WeatherController extends Controller
{
    public function show(Request $request)
    {
        $validated = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lon' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $key = config('services.openweather.key');

        if (! $key) {
            return response()->json([
                'message' => 'Weather is currently unavailable.',
            ], 503);
        }

        $lat = round((float) $validated['lat'], 4);
        $lon = round((float) $validated['lon'], 4);

        try {
            $weather = Cache::remember(
                "weather:{$lat}:{$lon}",
                now()->addMinutes(10),
                function () use ($lat, $lon, $key) {
                    $response = Http::connectTimeout(3)
                        ->timeout(10)
                        ->get(
                            'https://api.openweathermap.org/data/2.5/weather',
                            [
                                'lat' => $lat,
                                'lon' => $lon,
                                'units' => 'metric',
                                'appid' => $key,
                            ]
                        );

                    if (! $response->successful()) {
                        throw new RuntimeException('Weather request failed.');
                    }

                    $data = $response->json();
                    $temperature = data_get($data, 'main.temp');

                    if (! is_numeric($temperature)) {
                        throw new RuntimeException('Invalid weather response.');
                    }

                    return [
                        'place' => data_get($data, 'name') ?: 'Selected location',
                        'temperature' => $temperature,
                        'description' => data_get(
                            $data,
                            'weather.0.description',
                            'Unavailable'
                        ),
                        'humidity' => data_get($data, 'main.humidity'),
                    ];
                }
            );

            return response()->json($weather);
        } catch (ConnectionException | RuntimeException $exception) {
            return response()->json([
                'message' => 'Weather is currently unavailable.',
            ], 503);
        }
    }
}