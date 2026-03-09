<?php


namespace App\Services;

use App\Repositories\DetailsObjRepository;


class DetailsObjService extends Service
{
    private $detailsRepository;

    public function __construct()
    {
        $this->detailsRepository = new DetailsObjRepository();
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function findById(int $id)
    {
        return $this->detailsRepository->findById($id);
    }

    /**
     * @param array $array
     * @return void
     */
    public function update(array $data): void
    {
        $newData = $this->getValidate($data);
        $this->detailsRepository->update($newData);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function findByObjId(int $id): mixed
    {
        return $this->detailsRepository->findByObjId($id);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function store(array $data): mixed
    {
        $newData = $this->getValidate($data);
        return $this->detailsRepository->store($newData);
    }

    public function getValidate(array $data)
    {
        $alcoholValue = $data['alcohol'];
        $alcoholPrice = $data['alcohol_price'];

        // Форматируем значение для сохранения в JSON
        $alcoholJson = $alcoholValue;
        if ($alcoholValue == 2 && $alcoholPrice > 0) {
            $alcoholJson .= ':' . $alcoholPrice;
        }
        $data['alcohol'] = $alcoholJson;
        $moreValue = $data['more'];
        $morePrice = $data['more_price'];
        // Форматируем значение для сохранения в JSON
        $moreJson = $moreValue;
        if ($moreValue == 2 && $morePrice > 0) {
            $moreJson .= ':' . $morePrice;
        }
        $data['more'] = $moreJson;
        unset($data['more_price'], $data['alcohol_price']);

        return $data;
    }
}

