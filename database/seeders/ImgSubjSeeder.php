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
        $bigPhotoUrl = 'https://sun9-39.vkuserphoto.ru/s/v1/ig2/ENlg_KSgMX3E07e9j0Y659RZ7tVX5arEjuDGkhWx4pT1zdWXvnJwXmu_K83Hs5MfIMD1dfwnelJPMCa7uz_YEtxD.jpg?quality=96&as=32x21,48x32,72x48,108x72,160x107,240x160,360x240,480x320,540x360,640x427,720x480,1080x720,1200x800&from=bu&cs=1200x0';
        $smallPhotoUrl = 'https://sun9-39.vkuserphoto.ru/s/v1/ig2/ENlg_KSgMX3E07e9j0Y659RZ7tVX5arEjuDGkhWx4pT1zdWXvnJwXmu_K83Hs5MfIMD1dfwnelJPMCa7uz_YEtxD.jpg?quality=96&as=32x21,48x32,72x48,108x72,160x107,240x160,360x240,480x320,540x360,640x427,720x480,1080x720,1200x800&from=bu&cs=360x0';

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
