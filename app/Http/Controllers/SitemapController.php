<?php

namespace App\Http\Controllers;

use App\Models\Subj;
use App\Models\Obj;
use App\Models\GroupAddressObj;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;
use Exception;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $path = public_path('sitemap.xml');

        if (!file_exists($path)) {
            try {
                $this->generateSitemap($path);
            } catch (Exception $e) {
                Log::channel('error_file')->error('Sitemap generation failed', [
                    'path'    => $path,
                    'message' => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                ]);

                return $this->makeEmptySitemap();
            }
        }

        if (!file_exists($path) || filesize($path) === 0) {
            return $this->makeEmptySitemap();
        }

        return response(file_get_contents($path))
            ->header('Content-Type', 'application/xml')
            ->header('Cache-Control', 'public, max-age=86400');
    }

    public function regenerate(Request $request)
    {
        $path = public_path('sitemap.xml');

        try {
            // Удаляем старый, чтобы точно пересоздать
            if (file_exists($path)) {
                File::delete($path);
            }

            $this->generateSitemap($path);

            $urlCount = 0;
            if (file_exists($path)) {
                $content = file_get_contents($path);
                $urlCount = substr_count($content, '<loc>');
            }

            Log::channel('error_file')->info('Sitemap regenerated manually', [
                'path'  => $path,
                'urls'  => $urlCount,
                'ip'    => $request->ip(),
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success'  => true,
                    'message'  => 'Sitemap.xml успешно сгенерирован',
                    'urls'     => $urlCount,
                ]);
            }

            return redirect()->back()->with('message', "Sitemap.xml сгенерирован ({$urlCount} URL)");

        } catch (Exception $e) {
            Log::channel('error_file')->error('Manual sitemap regeneration failed', [
                'path'    => $path,
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success'  => false,
                    'message'  => 'Ошибка генерации: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('sitemap_error', 'Ошибка генерации sitemap: ' . $e->getMessage());
        }
    }

    private function generateSitemap(string $path): void
    {
        $baseUrl = rtrim(config('app.url', 'https://feast-boom.ru'), '/');

        if (empty($baseUrl)) {
            throw new Exception('APP_URL is not configured');
        }

        $urls = [$baseUrl];

        // Объекты — /show_obj/id{id}
        $objUrls = Obj::limit(5000)
            ->get()
            ->map(fn ($obj) => $baseUrl . '/show_obj/id' . $obj->id)
            ->all();

        // Опубликованные залы — /show_subj/id{id}
        $subjUrls = Subj::where('published', 1)
            ->limit(5000)
            ->get()
            ->map(fn ($subj) => $baseUrl . '/show_subj/id' . $subj->id)
            ->all();

        // Группы — /show_group/id{id}
        $groupUrls = GroupAddressObj::whereHas('subjs', function ($query) {
            $query->where('published', 1);
        })
            ->limit(5000)
            ->get()
            ->map(fn ($group) => $baseUrl . '/show_group/id' . $group->id)
            ->all();

        $urls = array_merge($urls, $objUrls, $subjUrls, $groupUrls);

        $xml = $this->buildSitemapXml($urls);

        if (!File::put($path, $xml)) {
            throw new Exception('Cannot write sitemap.xml: check permissions on public/');
        }
    }

    private function buildSitemapXml(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        foreach ($urls as $url) {
            $xml .= '  <url>' . PHP_EOL;
            $xml .= '    <loc>' . e($url) . '</loc>' . PHP_EOL;
            $xml .= '    <lastmod>' . date('Y-m-d') . '</lastmod>' . PHP_EOL;
            $xml .= '  </url>' . PHP_EOL;
        }

        $xml .= '</urlset>';

        return $xml;
    }

    private function makeEmptySitemap(): Response
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;
        $xml .= '</urlset>' . PHP_EOL;

        return response($xml)
            ->header('Content-Type', 'application/xml')
            ->header('Cache-Control', 'no-cache');
    }
}
