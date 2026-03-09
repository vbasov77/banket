<?php


namespace App\Services;


use Illuminate\Support\Facades\File;
use Intervention\Image\Image;

class FileService extends Service
{


    /**
     * @param object $file
     * @return string
     */
    public function addFile(object $file): string
    {
        $codeTheme = $this->findRandomCode();
        $guessExtensionImage = $file->guessExtension();// получили расширение фото
        $fileName = $codeTheme . '.' . $guessExtensionImage;

        //Записываем файлы
        $file->storeAs('images/news', $fileName, 'public');
        return $fileName;
    }

    /**
     * @param Image $file
     * @return string
     */
    public function temporaryFile(Image $file): string
    {

        $codeTheme = $this->findRandomCode();
        $fileName = $codeTheme . '.' . "png";
        $file->save('images/temporary/' . $fileName, (string)$file->encode());

        return $fileName;
    }

    /**
     * @param string $file
     */
    public function destroyImg(string $file)
    {
        unlink($file);
    }

    /**
     * @return string
     */
    public function findRandomCode(): string
    {
        $code = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';// Случайные символы
        return substr(str_shuffle($code), 0, 16);// Сгенерировали случайное имя для файлов
    }
}