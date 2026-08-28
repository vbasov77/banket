<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Repositories\ImgBanRepository;
use App\Repositories\KeyRepository;
use App\Requests\VkRequests;
use App\Services\AdminAlertService;
use App\Services\ImgBanSubjService;
use App\Services\ImgObjService;
use App\Services\ImgSubjService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;


class TestController extends Controller
{
    protected ImgObjService $imgObjService;
    protected ImgBanSubjService $imgBanSubjService;

    protected ImgSubjService $imgSubjService;

    protected VkRequests $vkRequests;

    protected KeyRepository $keyRepository;
    protected ImgBanRepository $imgBanRepository;

    protected AdminAlertService $adminAlertService;

    public function __construct(ImgObjService     $imgObjService,
                                ImgSubjService    $imgSubjService,
                                VkRequests        $vkRequests,
                                KeyRepository     $keyRepository,
                                ImgBanSubjService $imgBanSubjService,
                                ImgBanRepository  $imgBanRepository,
                                AdminAlertService $adminAlertService)
    {
        $this->imgObjService = $imgObjService;
        $this->imgSubjService = $imgSubjService;
        $this->vkRequests = $vkRequests;
        $this->keyRepository = $keyRepository;
        $this->imgBanSubjService = $imgBanSubjService;
        $this->imgBanRepository = $imgBanRepository;
        $this->adminAlertService = $adminAlertService;
    }

    /**
     * @return RedirectResponse
     */
    public function clearImg(): RedirectResponse
    {
        $imageBan = $this->imgBanSubjService->getAllImageBanIds();
        $ids = DB::table('img_ban_subj')
            ->select('big_id AS photo_id')
            ->whereNotNull('big_id')

            ->union(
                DB::table('img_ban_subj')
                    ->select('small_id AS photo_id')
                    ->whereNotNull('small_id')
            )

            ->union(
                DB::table('img_obj')
                    ->select('photo_id')
                    ->whereNotNull('photo_id')
            )
            ->pluck('photo_id')
            ->toArray();

        $orphanedIds = array_diff($imageBan, $ids);
        $orphanedIds = array_values($orphanedIds);

        if (count($orphanedIds) > 0) {
            $count = count($orphanedIds);
            for ($i = 0; $i < $count; $i++) {
                $this->imgBanRepository->delete($orphanedIds[$i]);
                sleep((int)0.5);
            }
            return redirect()->back()->with('message', $count . ' фото удалено');
        }

        return redirect()->back()->with('message', 'База чистая');
    }


    public function show()
    {
        return view('tests.img');
    }

    public function test()
    {
        return view('tests.test');
    }


    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws \HttpException
     */
    public function uploadImg(Request $request)
    {
        try {
            $this->imgSubjService->ImgSubjStore($request, 1);

            return redirect()->back()->with('message', 'Фото добавлено');
        } catch (\Exception $e) {
            Log::channel('error_file')->error(["Ошибка сохранения фото", $e]);
        }
    }

    public function testCities()
    {
        return view('tests.test_cities');
    }

    public function store(Request $request)
    {


    }

    public function delete(Request $request)
    {
        $imageId = 'xdPd0HF';

        $imagebanConfig = config('services.imageban');

        $url = 'https://api.imageban.ru/v1/image/delete/' . $imageId;

        Http::withHeaders([
            'Authorization' => 'Bearer ' . $imagebanConfig['client_secret'],
        ])->delete($url);

        return redirect()->route('test');
    }


    public function testMail()
    {
        try {
            Mail::raw('Hello!', function ($message) {
                $message->to('0120912@mail.ru')
                    ->subject('Test email');
            });
        } catch (\Exception $e) {

            Log::channel("error_file")->error( ['Failed to send email: '=> $e->getMessage()]);
            $this->adminAlertService->sendMsgToAdmin("Письмо не отправлено", "Письмо не отправлено");

            return redirect()->route('login');
        }


        $message = 'На почту 0120912@mail.ru отправлено письмо';
        return redirect()->back()->with('message', $message);
    }


}