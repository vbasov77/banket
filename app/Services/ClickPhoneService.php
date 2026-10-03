<?php

namespace App\Services;

use App\Models\ClickPhone;
use App\Models\Subj;
use App\Repositories\ClickPhoneRepository;

class ClickPhoneService
{
    private ClickPhoneRepository $clickPhoneRepository;

    /**
     * @param ClickPhoneRepository $clickPhoneRepository
     */
    public function __construct(ClickPhoneRepository $clickPhoneRepository)
    {
        $this->clickPhoneRepository = $clickPhoneRepository;
    }


    /**
     * Записать клик по телефону. Возвращает количество кликов субъекта.
     * @param int $subjId
     * @return void
     *
     */
    public function recordClick(int $subjId, string $ip): bool
    {
        if ($this->clickPhoneRepository->existsToday($subjId, $ip)) {
            return false; // уже кликали сегодня — не дублируем
        }

        $click = new ClickPhone(['subj_id' => $subjId, 'ip' => $ip]);
        $click->save();

        return true;
    }

}
