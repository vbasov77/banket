<?php

declare(strict_types=1);

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;


class StorePerformerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // проверка прав позже: middleware auth + is_admin
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:catalog_categories,id,published,1'],
            'city_id'     => ['required', 'integer', 'exists:cities,id'],
            'name'        => ['required', 'string', 'min:2', 'max:255'],
            'description' => ['nullable', 'string', 'min:20', 'max:10000'],
            'phone'       => ['required', 'string', 'min:18', 'max:18'],
            'video_url'   => ['nullable', 'array', 'max:10'],
            'video_url.*' => ['nullable', 'url', 'max:500'],
            'is_active'   => ['nullable'],
        ];
    }

    public function attributes(): array
    {
        return [
            'category_id' => 'Категория',
            'city_id'     => 'Город',
            'name'        => 'Имя или название',
            'description' => 'Описание',
            'phone'       => 'Телефон',
            'video_url'   => 'Ссылки на видео',
            'video_url.*' => 'Ссылка на видео',
            'published'   => 'Публикация',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Поле «:attribute» обязательно для заполнения',
            'integer'  => 'Поле «:attribute» должно быть целым числом',
            'string'   => 'Поле «:attribute» должно быть строкой',
            'min'      => 'Поле «:attribute» должно содержать не менее :min символов',
            'max'      => 'Поле «:attribute» не должно превышать :max символов',
            'url'      => 'Поле «:attribute» должно содержать корректный URL (https://...)',
            'exists'   => 'Указанный :attribute не существует',
            'regex'    => 'Поле «:attribute» содержит недопустимые символы',
            'boolean'  => 'Поле «:attribute» должно содержать логическое значение',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Соцстраница удалена — телефон теперь обязательный контакт

            // Ссылки только на VK
            foreach ((array) $this->input('video_url', []) as $i => $url) {
                if ($url && ! preg_match('/^https?:\/\/(vk\.com|vkvideo\.ru|vk\.ru)\//i', $url)) {
                    $validator->errors()->add("video_url.{$i}", 'Ссылка должна вести на VK или VK Видео.');
                }
            }
        });
    }
}
