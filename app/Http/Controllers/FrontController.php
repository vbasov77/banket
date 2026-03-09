<?php

declare(strict_types=1);

namespace App\Http\Controllers;


use App\Services\ObjService;


class FrontController extends Controller
{
    private $objService;

    /**
     * FrontController constructor.
     */
    public function __construct()
    {
        $this->objService = new ObjService();
    }

    public function show()
    {
        $data = $this->objService->findObjsWithDetails();

        return view('front', ['data' => $data]);
    }

}