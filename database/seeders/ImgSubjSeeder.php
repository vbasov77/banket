<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImgSubjSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Получаем все ID субъектов из таблицы subjs
        $subjectIds = DB::table('subjs')->pluck('id');

        // Единая ссылка на фото
        $photoUrl = 'https://sun9-32.userapi.com/s/v1/ig2/kaZklHEa5QIRcgmw55sQDvO3Amw8SSJR6x3OmT6BhQIgLaiSJVXk3-A8mkpp25_3Fut_Ytwo60MkgJOMkzsqH8M4.jpg?quality=96&as=32x21,48x32,72x48,108x72,160x107,240x160,360x240,480x320,540x360,640x427,720x480,1080x720,1200x800&from=bu';

        $recordsToInsert = [];

        foreach ($subjectIds as $subjectId) {
            for ($i = 1; $i <= 5; $i++) {
                $recordsToInsert[] = [
                    'subj_id' => $subjectId,
                    'photo_id' => rand(1000, 9999), // случайный ID фото
                    'path' => $photoUrl,
                    'position' => $i, // позиция фото (1–5)
                    'created_at' => now(),
                ];
            }
        }

        // Массовая вставка записей в таблицу img_subj
        DB::table('img_subj')->insert($recordsToInsert);
    }
}
