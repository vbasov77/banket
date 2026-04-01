<?php

declare(strict_types=1);

namespace App\Http\Controllers;


use App\Services\ObjService;
use Illuminate\Http\Request;


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

    public function show(Request $request)
    {
        $data = $this->objService->findObjsWithDetails();
        return view('front', ['data' => $data]);
    }

}