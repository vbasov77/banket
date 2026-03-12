<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\BookingService;
use App\Services\FileService;
use App\Services\ImageService;
use App\Services\ImgObjService;
use App\Services\ImgSubjService;
use App\Services\NewsService;
use App\Services\SettingsService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;


class TestController extends Controller
{
    private $imgObjService;
    private $imageService;

    private $imgSubjService;

    public function __construct()
    {
        $this->imgObjService = new ImgObjService();
        $this->imageService = new ImageService();
        $this->imgSubjService = new ImgSubjService();
    }


    public function show()
    {
        return view('tests.img');
    }

    public function test()
    {
        return view('tests.test');
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






}