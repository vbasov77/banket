<?php

namespace App\Services;

use App\Models\{User, UserContact};

class ContactService
{
    /**
     * Синхронизация: заполненные поля создаются/обновляются,
     * пустые — удаляются.
     */
    public function sync(User $user, array $data): void
    {
        foreach (UserContact::TYPES as $type) {
            $value = isset($data[$type]) ? $this->normalize($type, trim($data[$type])) : '';

            if ($value !== '') {
                $user->contacts()->updateOrCreate(
                    ['type' => $type],
                    ['value' => $value]
                );
            } else {
                // поле очистили в форме — контакт удаляем
                $user->contacts()->where('type', $type)->delete();
            }
        }
    }

    private function normalize(string $type, string $value): string
    {
        return match ($type) {
            'whatsapp' => preg_replace('/\D+/', '', $value),
            'telegram' => ltrim(trim($value), '@'),
            default    => trim($value), // max, vk
        };
    }
}
