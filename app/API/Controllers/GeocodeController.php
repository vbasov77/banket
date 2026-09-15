<?php

namespace App\API\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GeocodeController extends Controller
{
    public function search(Request $request)
    {
        $request->validate(['q' => 'required|string|max:255']);

        $response = Http::timeout(8)
            ->withHeaders(['User-Agent' => 'FeastBoom/1.0'])
            ->retry(3, 1000)
            ->get('https://nominatim.openstreetmap.org/search', [
                'q'              => $request->input('q'),
                'format'         => 'json',
                'limit'          => 1,
                'accept-language' => 'ru',
            ]);

        if (!$response->ok()) {
            return response()->json([
                'success' => false,
                'message' => 'Сервис геокодирования недоступен',
            ], 503);
        }

        $data = $response->json();

        if (empty($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Адрес не найден',
            ]);
        }

        return response()->json([
            'success' => true,
            'lat'     => (float) $data[0]['lat'],
            'lon'     => (float) $data[0]['lon'],
        ]);
    }
}
