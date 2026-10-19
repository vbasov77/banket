<?php

namespace App\Http\Controllers;

use App\Services\GroupAddressObjService;
use App\Services\SubjService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
        // 1. Валидация id: не число или <= 0 — это не ошибка сервера, а 404
        if (!is_numeric($request->id) || (int)$request->id <= 0) {
            abort(404, 'Группа не найдена');
        }
        $id = (int)$request->id;

        try {
            $result = $this->groupAddressObjService->findSubjectsByGroupId($id);
            Log::channel('info_file')->info([$result]);
            // 2. Группа не найдена — честный 404, а не шаблон с group => null
            if (empty($result['group_details'])) {
                abort(404, 'Группа не найдена');
            }

            // 3. metaDescription считаем только после проверки, что группа есть
            $metaDescription = $this->groupAddressObjService->findMetaDescription($result);

            // 4. Ближайшие объекты — только если есть координаты и сам объект
            $nearestObjects = null;
            if (!empty($result['group_details']['longitude'])
                && !empty($result['group_details']['obj']['id'])
            ) {
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
                'metaDescription' => $metaDescription,
            ]);
        } catch (HttpException $e) {
            // 5. 404/500 от abort не логируем как баг и не перекодируем — отдаём как есть
            throw $e;
        } catch (\Illuminate\Database\QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в GroupAddressObjController@show: ' . $e->getMessage(),
                [
                    'sql_query' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'input_data' => $request->all(),
                    'user_id' => auth()->id(),
                ]
            );

            abort(500, 'Ошибка при получении данных группы');
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Неожиданная ошибка в GroupAddressObjController@show: ' . $e->getMessage(),
                [
                    'input_data' => $request->all(),
                    'user_id' => auth()->id(),
                    'exception_class' => get_class($e),
                ]
            );

            abort(500, 'Произошла внутренняя ошибка сервера');
        }
    }
}
