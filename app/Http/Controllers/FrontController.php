<?php

declare(strict_types=1);

namespace App\Http\Controllers;


use App\Services\ObjService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class FrontController extends Controller
{
    private ObjService $objService;

    /**
     * FrontController constructor.
     */
    public function __construct(ObjService $objService)
    {
        $this->objService = $objService;
    }

    public function show()
    {
        try {
            $data = $this->objService->findObjsWithDetails();

            return view('front', ['data' => $data]);
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в FavoriteController@show: ' . $e->getMessage(),
                [
                    'sql_query' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'trace' => $e->getTraceAsString()
                ]
            );
            return response()->view('errors.500', [], 500);
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Неожиданная ошибка в FavoriteController@show: ' . $e->getMessage(),
                [
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString()
                ]
            );
            return response()->view('errors.500', [], 500);
        }
    }


}