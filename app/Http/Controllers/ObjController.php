<?php

namespace App\Http\Controllers;

use App\Http\Requests\Obj\CreateObjRequest;
use App\Http\Requests\Obj\EditObjRequest;
use App\Models\Obj;
use App\Services\ImgObjService;
use App\Services\ObjService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use PHPUnit\Exception;

class ObjController extends Controller
{
    private $objService;
    private $imgService;


    public function __construct()
    {
        $this->objService = new ObjService();
        $this->imgService = new ImgObjService();
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
    public function create(): View
    {
        return view('objects.create', ['user' => Auth::id()]);
    }


    public function store(CreateObjRequest $request)
    {
        $obj = Obj::create($request->validated());
        if ($obj) {
            return redirect()->route("create.details_obj", ['obj' => $obj]);
        }
    }


    public function show(Request $request): View
    {
        $obj = $this->objService->findById($request->id);
        $images = $this->imgService->findImgByObjId($request->id);

        return \view('objects.show', ['obj' => $obj, 'images' => $images]);
    }


    /**
     * @param Request $request
     * @return View
     */
    public function edit(Request $request): View
    {
        $obj = $this->objService->findByIdOnlyObj($request->id);

        return \view('objects.edit', ['obj' => $obj]);
    }


    /**
     * @param EditObjRequest $request
     * @return RedirectResponse|string
     */
    public function update(EditObjRequest $request)
    {
        try {
            $this->objService->update($request->validated(), $request->id);

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
}
