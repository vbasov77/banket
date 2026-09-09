<?php


namespace App\Services;


use App\Models\Message;
use App\Repositories\MessageRepository;

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

    public function chat(int $currentUserId, int $toUserId, int $limit = 20, ?int $page = 1): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->messageRepository->getChatMessages($currentUserId, $toUserId, $limit, $page);
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

    public function getUnreadMessageIdsForChat(int $userId, int $partnerId): array
    {
        return Message::query()
            ->where('to_user_id', $userId)           // сообщения, адресованные текущему пользователю
            ->where('from_user_id', $partnerId)      // от конкретного собеседника
            ->where('status', 0)                    // только непрочитанные
            ->pluck('id')                           // получаем только массив ID
            ->toArray();                            // превращаем в обычный PHP-массив
    }

    /**
     * @param array $idsMessages
     * @return void
     */
    public function changeStatus(array $idsMessages): void
    {
        foreach ($idsMessages as $idMsg) {
            Message::where('id', $idMsg)->update(['status' => 1]);
        }

    }

    /**
     *  Нормализует входные ID в чистый числовой массив.
     *  Принимает: array, JSON-строку, одиночное число.
     * @param mixed $ids
     * @return array
     */

    private function normalizeIds(mixed $ids): array
    {
        // Строку-JSON разбираем в массив
        if (is_string($ids)) {
            $ids = json_decode($ids, true);
        }

        // Приводим к массиву, оставляем только числа
        $ids = array_filter((array)$ids, 'is_numeric');

        // Пересобираем ключи, чтобы это был простой список
        return array_values($ids);
    }

    /**
     * Удаляет сообщения по переданным ID.
     * @return array [deletedCount => int]
     */
    public function deleteMessages(mixed $rawIds): array
    {
        $ids = $this->normalizeIds($rawIds);

        if (empty($ids)) {
            throw new \InvalidArgumentException('Не переданы корректные ID сообщений');
        }

        $deletedCount = Message::whereIn('id', $ids)->delete();

        return ['deleted_count' => $deletedCount, 'ids' => $ids];
    }

}