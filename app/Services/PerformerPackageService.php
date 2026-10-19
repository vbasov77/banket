<?php

namespace App\Services;

use App\Repositories\PerformerPackageRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class PerformerPackageService
{
    public function __construct(
        protected PerformerPackageRepository $repository,
    ) {}


    public function validatePackages(?array $packages): array
    {
        $clean = [];

        foreach ($packages ?? [] as $i => $package) {
            if (trim((string)($package['title'] ?? '')) === ''
                && ($package['price_min'] ?? '') === ''
                && ($package['price_max'] ?? '') === '') {
                continue; // строка пуста — пропускаем
            }

            $validator = Validator::make(['package' => $package], [
                'package.title'            => 'required|string|max:200',
                'package.description'      => 'nullable|string|max:2000',
                'package.price_min'        => 'nullable|integer|min:0',
                'package.price_max'        => 'nullable|integer|min:0',
                'package.is_addon'         => 'nullable|boolean',
            ], [
                'package.title.required' => "Пакет №" . ($i + 1) . ": укажите название",
                'package.price_min.min'  => "Пакет №" . ($i + 1) . ": цена не может быть отрицательной",
                'package.price_max.min'  => "Пакет №" . ($i + 1) . ": цена не может быть отрицательной",
            ]);

            if ($validator->fails()) {
                throw ValidationException::withMessages(
                    $validator->errors()->all()
                );
            }

            $data = $validator->validated()['package'];

            if (($data['price_max'] ?? null) !== null
                && ($data['price_min'] ?? null) !== null
                && $data['price_max'] < $data['price_min']) {
                throw ValidationException::withMessages([
                    "Пакет №" . ($i + 1) . ": «цена до» меньше «цены от»",
                ]);
            }

            $clean[] = [
                'title'       => $data['title'],
                'description' => $data['description'] ?? null,
                'price_min'   => isset($data['price_min']) ? (int)$data['price_min'] : null,
                'price_max'   => isset($data['price_max']) ? (int)$data['price_max'] : null,
                'is_addon'    => (bool)($data['is_addon'] ?? false),
            ];
        }

        return $clean;
    }

    /**
     * Сохранение пакетов исполнителя + денормализация минимальной цены.
     */
    public function syncForPerformer(int $performerId, ?array $rawPackages): void
    {
        $packages = $this->validatePackages($rawPackages);

        $this->repository->syncForPerformer($performerId, $packages);
    }
}
