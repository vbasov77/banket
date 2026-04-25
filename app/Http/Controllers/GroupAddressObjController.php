<?php

namespace App\Http\Controllers;

use App\Services\GroupAddressObjService;
use App\Services\SubjService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class GroupAddressObjController extends Controller
{
    private GroupAddressObjService $groupAddressObjService;
    private SubjService $subjService;

    public function __construct(GroupAddressObjService $groupAddressObjService, SubjService $subjService)
    {
        $this->groupAddressObjService = $groupAddressObjService;
        $this->subjService = $subjService;
    }

    /**
     * @param Request $request
     * @return View
     */
    public function show(Request $request): View
    {
        try {
            $id = $request->id;
            $result = $this->groupAddressObjService->findSubjectsByGroupId($id);

            if (!$result['group_details']) {
                return view('objects.groups.show', [
                    'group' => null,
                    'details_obj' => null,
                    'subjs' => [],
                    'error' => 'Группа не найдена'
                ]);
            }

            $nearestObjects = null;
            if (!empty($result['group_details']['longitude'])) {
                $nearestObjects = $this->subjService->findNearestObjects(
                    $result['group_details']['latitude'],
                    $result['group_details']['longitude'],
                    $result['group_details']['obj']['id']
                );

            }

            return view('objects.groups.show', [
                'group' => $result['group_details'],
                'subjs' => $result['subjs'],
                'nearestObjects' => $nearestObjects,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в GroupAddressObjController@show: ' . $e->getMessage(),
                [
                    'sql_query' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'input_data' => $request->all(),
                    'user_id' => auth()->id()
                ]
            );

            return view('objects.groups.show', [
                'group' => null,
                'subjs' => [],
                'error' => 'Ошибка при получении субъектов группы'
            ]);
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Неожиданная ошибка в GroupAddressObjController@show: ' . $e->getMessage(),
                [
                    'input_data' => $request->all(),
                    'user_id' => auth()->id(),
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString()
                ]
            );

            return view('objects.groups.show', [
                'group' => null,
                'subjs' => [],
                'error' => 'Произошла внутренняя ошибка сервера'
            ]);
        }
    }
}
