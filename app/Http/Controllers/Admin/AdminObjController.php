<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ImgObjService;
use App\Services\ObjService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminObjController extends Controller
{
    private ObjService $objService;


    public function __construct(ObjService $objService)
    {
        $this->objService = $objService;

    }

    public function show(Request $request)
    {
        $error = null;
        $data = $this->objService->findMySubjs($request->id);

        $message = session('message');
        if (!empty($request->error)) {
            $error = $request->error;
        }
        return view('objects.subjects.my_subjs', [
            'data' => $data,
            'message' => $message,
            'error' => $error,
        ]);
    }

    public function findById()
    {
        $lastId = DB::table('objs')->max('id') ?? 0;
        return view('admin.obj.find_id', compact('lastId'));
    }
}