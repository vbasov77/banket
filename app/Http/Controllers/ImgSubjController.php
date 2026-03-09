<?php

namespace App\Http\Controllers;


use App\Models\ImgSubj;
use App\Services\ImageService;
use App\Services\ImgSubjService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ImgSubjController extends Controller
{
    private $imgSubjService;
    private $imgService;

    public function __construct()
    {
        $this->imgSubjService = new ImgSubjService();
        $this->imgService = new ImageService();
    }


    public function edit(Request $request)
    {
        $subj = $request->id;
        $images = $this->imgSubjService->findImgByObjId($subj);

        return view('img_subj.edit', ['subj' => $subj, 'images' => $images]);
    }

    public function create(Request $request)
    {

    }


    public function imgOrderChange(Request $request)
    {
        $data = $request->input('order');
        foreach ($data as $index => $id) {
            DB::table('img_subj')->where('id', $id)->update(['position' => $index]);
        }

        return response()->json([
            'message' => 'Порядок изменён.',
            'alert-type' => 'success'
        ]);
    }

    public function imgSubjStore(Request $request)
    {
        
        if ($request->file('img')) {
            $data = $this->imgSubjService->ImgSubjStore($request, $request->id);
            $res = ['path' => $data[0], 'id' => $data[1], 'message' => null];
        }
        return response()->json($res);
    }


    public function destroy(Request $request)
    {
        if ($request->id) {
            ImgSubj::where('id', $request->id)->delete();
            $res = ['answer' => 'ok'];

            return response()->json($res);
        }

    }
}
