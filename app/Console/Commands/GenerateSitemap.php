<?php

namespace App\Console\Commands;

use App\Models\Subj;
use App\Models\Obj;
use App\Models\GroupAddressObj;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Exception;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate sitemap.xml for feast-boom.ru';

    public function handle(): int
    {
        try {
            $path = public_path('sitemap.xml');
            $baseUrl = 'https://feast-boom.ru';

            $urls = [$baseUrl];

            // Объекты (рестораны) — /show_obj/id{id}
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

            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

            foreach ($urls as $url) {
                $xml .= '  <url>' . PHP_EOL;
                $xml .= '    <loc>' . e($url) . '</loc>' . PHP_EOL;
                $xml .= '    <lastmod>' . date('Y-m-d') . '</lastmod>' . PHP_EOL;
                $xml .= '  </url>' . PHP_EOL;
            }

            $xml .= '</urlset>';

            if (!File::put($path, $xml)) {
                throw new Exception('Cannot write sitemap.xml: check permissions on public/');
            }

            $this->info('Sitemap generated successfully.');
            return self::SUCCESS;

        } catch (Exception $e) {
            Log::channel('error_file')->error('Sitemap generation failed (command)', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            $this->error('Sitemap generation failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
