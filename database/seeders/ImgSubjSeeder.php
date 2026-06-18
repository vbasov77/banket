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

        $bigPhotoId = 'adadadeqwd';
        $smallPhotoId = 'csnckwncskhkl';
        // Единая ссылка на фото
        $bigPhotoUrl = 'https://i2.imageban.ru/out/2026/06/18/00eeb8a3c90a333f5debfc92a7d287b9.jpg';
        $smallPhotoUrl = 'https://i3.imageban.ru/out/2026/06/18/e4906281254c2f226d8762df717de91a.jpg';

        $recordsToInsert = [];

        foreach ($subjectIds as $subjectId) {
            for ($i = 1; $i <= 5; $i++) {
                $recordsToInsert[] = [
                    'subj_id' => $subjectId,
                    'big_id' => $bigPhotoId, // случайный ID фото
                    'big_img' => $bigPhotoUrl,
                    'small_id' => $smallPhotoId, // случайный ID фото
                    'small_img' => $smallPhotoUrl,
                    'position' => $i, // позиция фото (1–5)
                ];
            }
        }

        // Массовая вставка записей в таблицу img_subj
        DB::table('img_ban_subj')->insert($recordsToInsert);
    }
}
