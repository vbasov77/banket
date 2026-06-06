<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\UserVk;
use App\Repositories\KeyRepository;
use App\Requests\VkRequests;
use App\Services\ImgObjService;
use App\Services\ImgSubjService;
use Illuminate\Http\Request;


class TestController extends Controller
{
    protected ImgObjService $imgObjService;

    protected ImgSubjService $imgSubjService;

    protected VkRequests $vkRequests;

    protected KeyRepository $keyRepository;

    public function __construct(ImgObjService  $imgObjService,
                                ImgSubjService $imgSubjService,
                                VkRequests     $vkRequests,
                                KeyRepository  $keyRepository)
    {
        $this->imgObjService = $imgObjService;
        $this->imgSubjService = $imgSubjService;
        $this->vkRequests = $vkRequests;
        $this->keyRepository = $keyRepository;
    }


    public function show()
    {
        return view('tests.img');
    }

    public function test()
    {
        $user = UserVk::where('user_id', 1)->first();
        $a = "";//"photo642803932_457239437, photo642803932_457239436, photo642803932_457239441, photo642803932_457239438, photo642803932_457239439, photo642803932_457239440, photo642803932_457239442";

        $params = [
            'idVk' => $user->vk_id,
            'group_id' => 227627516,
            'owner_id' => "-" . 227627516,
            'message' => "Привет",
            'attachments' => $a,
            'access_token' => $user->access_token,// 'vk1.a.SVS7VRWmfU_OP1raazxwVTePla_hvsCb2KRxQdVmslPYoN96CZu1HE5bSgxZE7NoOGzTGDTwciEnsuqz65WxEjyxV9c8hSzNlet825tlOyK8vsWULA78vw6LC7cre_XSfOJ3IRUfee06KyH7XMuYjWjoC1V8Tjr-V7iWdVcIzr_0ifoNanNIgVljbY-_4L0doBZcJPNUL7krTJN3nG57pA',
            'v' => '5.131'
        ];

        $result = $this->vkRequests->SendWallPost($params);
        dd($result);
//        return view('tests.test');
    }

    public function testCities()
    {
        return view('tests.test_cities');
    }

    public function store(Request $request)
    {

        $data = $this->imgSubjService->ImgSubjStore($request, $request->id);
        dd($data);
    }

    public function checkToken()
    {

    }


}