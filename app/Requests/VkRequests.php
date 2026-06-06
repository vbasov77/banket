<?php

namespace App\Requests;


use App\Repositories\KeyRepository;
use App\Repositories\RequestRepository;

class VkRequests extends Request
{
    protected KeyRepository $keyRepository;
    protected RequestRepository $requestRepository;

    /**
     * @param KeyRepository $keyRepository
     * @param RequestRepository $requestRepository
     */
    public function __construct(KeyRepository $keyRepository, RequestRepository $requestRepository)
    {
        $this->keyRepository = $keyRepository;
        $this->requestRepository = $requestRepository;
    }


    public function checkToken(array $data): bool
    {
        $url = "https://api.vk.com/method/secure.checkToken";
        $result = json_decode($this->requestRepository->post($url, $data));

        return true;
    }

    public function SendWallPost($data)
    {

//        /*Сначала проверяем не состою ли в группе, если нет, то вступаю.*/
//        $urlIsMember = "https://api.vk.com/method/groups.isMember";
//        $dat = [
//            'group_id' => $data ['group_id'],
//            'user_id' => $data ['idVk'],
//            'extended' => 1,
//            'access_token' => $data ['access_token'],
//            'v' => $data ['v'],
//        ];
//        $result = json_decode($this->requestRepository->post($urlIsMember, $dat));
//
//        if (!empty($result->response->member) == 0) {
//            $ur = "https://api.vk.com/method/groups.join";
//            $par = [
//                'group_id' => $data ['group_id'],
//                'not_sure' => 1,
//                'access_token' => $data ['access_token'],
//                'v' => 5.131,
//            ];
//            json_decode($this->requestRepository->post($ur, $par));
//            sleep(20);
//        }

        $url = "https://api.vk.com/method/wall.post";

        $params = [
            'owner_id' => $data['owner_id'],
            'message' => $data['message'],
            'attachments' => $data['attachments'],
            'access_token' => $data['access_token'],
            'v' => $data ['v']
        ];

        return json_decode($this->requestRepository->post($url, $params));
    }

}