<?php

namespace App\Http\Controllers;

class AppController extends Controller
{
    public function android()
    {
        // Путь к APK в storage/app/public/feastboom.apk
        // Или URL на внешний CDN
        $apkUrl = asset('storage/feastboom.apk');
        $apkVersion = '1.0.0';
        $apkSize = '10 МБ'; // можно вычислять динамически

        return view('app.android', compact('apkUrl', 'apkVersion', 'apkSize'));
    }

    public function download()
    {
        $path = storage_path('app/public/feastboom.apk');

        if (!file_exists($path)) {
            abort(404, 'APK файл не найден');
        }

        return response()->download($path, 'feastboom.apk', [
            'Content-Type' => 'application/vnd.android.package-archive',
        ]);
    }
}
