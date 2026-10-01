<?php

namespace App\Services;

use App\Models\Action;
use App\Repositories\ActionRepository;

class ActionService
{
    public function __construct(
        private readonly ActionRepository $actionRepository
    )
    {
    }

    public function getActionByObjId(int $objId): ?Action
    {
        return $this->actionRepository->getByObjId($objId);
    }

    public function createAction(int $objId, string $actionsText): Action
    {
        return $this->actionRepository->create([
            'obj_id' => $objId,
            'actions' => $this->normalizeText($actionsText),
        ]);
    }

    /**
     * @param string $actionsText
     * @param int $objId
     * @return void
     */
    public function updateAction(string $actionsText, int $objId): void
    {
        Action::where('obj_id', $objId)
            ->update(['actions' => $this->normalizeText($actionsText)]);
    }

    public function deleteAction(Action $action): void
    {
        $this->actionRepository->delete($action);
    }

    private function normalizeText(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        return preg_replace('/\n{2,}/', "\n", trim($text));
    }
}
