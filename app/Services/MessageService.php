<?php


namespace App\Services;


use App\Models\AddressSubj;
use App\Models\GroupAddressObj;
use App\Models\Obj;
use App\Models\Subj;
use App\Repositories\AddressSubjRepository;
use App\Repositories\MapRepository;
use App\Repositories\MessageRepository;
use App\Requests\Request;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MessageService extends Service
{
    private MessageRepository $messageRepository;

    /**
     * @param MessageRepository $messageRepository
     */
    public function __construct(MessageRepository $messageRepository)
    {
        $this->messageRepository = $messageRepository;
    }

    /**
     * @return array
     */
    public function findMyMessages(): array
    {
        return $this->messageRepository->findMyMessages();
    }

    public function chat(int $currentUserId, int $toUserId): array
    {
        return $this->messageRepository->getChatMessages($currentUserId, $toUserId);
    }

    public function markRead(int $currentUserId, array $messageIds): int
    {
        return $this->messageRepository->markAsReadForUser($currentUserId, $messageIds);
    }

    /**
     * @param array $data
     * @return array
     */
    public function store(array $data): array
    {
        return $this->messageRepository->store($data);
    }
}