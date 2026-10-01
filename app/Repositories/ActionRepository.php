<?php

namespace App\Repositories;

use App\Models\Action;

class ActionRepository
{
    public function getByObjId(int $objId): ?Action
    {
        return Action::where('obj_id', $objId)->first();
    }

    public function create(array $data): Action
    {
        return Action::create($data);
    }

    public function update(Action $action, array $data): Action
    {
        $action->update($data);
        return $action;
    }

    public function delete(Action $action): void
    {
        $action->delete();
    }
}
