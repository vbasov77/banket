<?php

namespace App\Http\Controllers;

use App\Http\Requests\DetailsObj\CreateDetailsObjRequest;
use App\Http\Requests\DetailsObj\EditDetailsObjRequest;
use App\Services\DetailsObjService;
use App\Services\ImgObjService;
use App\Services\ObjService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PHPUnit\Exception;

class DetailsObjController extends Controller
{
    private $objService;
    private $imgService;
    private $detailsObjService;


    public function __construct()
    {
        $this->objService = new ObjService();
        $this->imgService = new ImgObjService();
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
    public function create(): View
    {
        $obj = $this->objService->findObjByUserId();

        return \view('details_obj.create', ['obj' => $obj]);
    }


    public function store(CreateDetailsObjRequest $request)
    {
        $data = $request->validated();
        $newObject = $this->detailsObjService->store($data);

        return redirect()->route('create.subj');
    }

    private function parseJsonValue(string $jsonValue)
    {
        if (strpos($jsonValue, ':') !== false) {
            [$value, $price] = explode(':', $jsonValue);
            return ['value' => $value, 'price' => (float)$price];
        }

        return ['value' => $jsonValue, 'price' => 0];
    }

    public function show(Request $request): View
    {
        dd($_POST);
    }


    /**
     * @param Request $request
     * @return View
     */
    public function edit(Request $request): View
    {
        $obj = $this->detailsObjService->findById($request->id);
        $alcoholData = $this->parseJsonValue($obj->alcohol);
        $moreData = $this->parseJsonValue($obj->more);

        return \view('details_obj.edit', ['obj' => $obj, 'alcoholValue' => $alcoholData['value'],
            'alcoholPrice' => $alcoholData['price'], 'moreValue' => $moreData['value'],'morePrice' => $moreData['price']]);
    }


    /**
     * @param EditDetailsObjRequest $request
     * @return RedirectResponse|string
     */
    public function update(EditDetailsObjRequest $request): RedirectResponse|string
    {
        try {
            $data = $request->validated();
            $this->detailsObjService->update($data);
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
