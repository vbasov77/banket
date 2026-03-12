<?php

namespace App\Http\Controllers;

use App\Http\Requests\Obj\EditObjRequest;
use App\Http\Requests\Subj\CreateSubjRequest;
use App\Http\Requests\Subj\EditSubjRequest;
use App\Models\ImgObj;
use App\Models\ImgSubj;
use App\Models\Obj;
use App\Models\Subj;
use App\Services\DetailsObjService;
use App\Services\ImgObjService;
use App\Services\ImgSubjService;
use App\Services\ObjService;
use App\Services\SubjService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use PHPUnit\Exception;

class SubjController extends Controller
{
    private $subjService;
    private $objService;
    private $imgSubjService;
    private $detailsObjService;


    public function __construct()
    {
        $this->subjService = new SubjService();
        $this->imgSubjService = new ImgSubjService();
        $this->objService = new ObjService();
        $this->detailsObjService = new DetailsObjService();
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * @return View
     */
    public function create(Request $request): View
    {
        $objId = $this->objService->findIdObjByUserId();

        return view('objects.subjects.create', ['objId' => $objId]);
    }


    public function store(CreateSubjRequest $request)
    {

        $subj = Subj::create($request->validated());
        if ($subj) {
            return redirect()->route('edit.img_subj', ['id' => $subj->id]);
        }
    }


    public function show(Request $request): View
    {
        $id = $request->id;
        $subj = $this->subjService->findById($id);

        return view('objects.subjects.show', [
            'subj' => $subj,
        ]);
    }

    public function myObj(): View
    {
        $objId = $this->objService->findIdObjByUserId(Auth::user()->id);
        $data = null;

        if ($objId) {
            $data = $this->subjService->findMySubjs($objId);
        }

        return \view('objects.subjects.my_subjs', ['data' => $data]);
    }

    public function edit(Request $request)
    {
        $id = $request->id;
        $subj = $this->subjService->findByIdForEdit($id);
        $images = $this->subjService->existsImg($id);

        return \view('objects.subjects.edit', ['subj' => $subj, 'images' => $images]);
    }


    public function update(EditSubjRequest $request)
    {
        try {
            $this->subjService->update($request->validated(), (int)$request->input('subj_id'));

            return redirect()->route('my.obj');
        } catch (Exception $e) {
            return "No";
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function takeOff(Request $request)
    {
        try {
            Subj::where('id', $request->id)->update(['published' => 0]);
            return response()->json([
                'answer' => 'ok',
                'message' => 'Публикация снята'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'answer' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function published(Request $request)
    {
        try {
            Subj::where('id', $request->id)->update(['published' => 1]);
            return response()->json([
                'answer' => 'ok',
                'message' => 'Публикация снята'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'answer' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


}
