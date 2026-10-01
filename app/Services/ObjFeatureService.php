<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Obj;
use App\Models\ObjFeature;
use Illuminate\Support\Facades\Log;

class ObjFeatureService
{
    /**
     *
     * @param array $data
     * @return ObjFeature
     * @throws \RuntimeException
     */
    public function upsert(array $data): ObjFeature
    {
        $objId = $data['obj_id'] ?? null;
        if (!$objId) {
            throw new \RuntimeException('ID объекта (obj_id) обязателен.');
        }

        $obj = Obj::find($objId);
        if (!$obj) {
            Log::channel('error_file')->error('Объект не найден при upsert особенностей', [
                'obj_id' => $objId,
            ]);
            throw new \RuntimeException("Объект с ID {$objId} не найден.");
        }

        // Нормализация массивов для JSON-полей
        $data['textile_package'] = $data['textile_package'] ?? [];
        $data['tables']           = $data['tables'] ?? [];
        $data['equipment']        = $data['equipment'] ?? [];
        $data['kids']             = $data['kids'] ?? [];
        $data['location']         = $data['location'] ?? [];

        return ObjFeature::updateOrCreate(
            ['obj_id' => $obj->id],
            $data
        );
    }

    /**
     * @param int $objId
     * @return bool
     */
    public function existsForObj(int $objId): bool
    {
        return ObjFeature::where('obj_id', $objId)->exists();
    }
}
