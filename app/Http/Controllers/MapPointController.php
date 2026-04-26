<?php

namespace App\Http\Controllers;

use App\Models\AddressSubj;
use App\Models\GroupAddressObj;
use App\Models\MapPoint;
use App\Models\Subj;
use App\Repositories\AddressSubjRepository;
use App\Repositories\MapRepository;
use App\Services\MapService;
use App\Services\SubjService;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\Factory;
use Illuminate\View\View;
use function Symfony\Component\Translation\t;

class MapPointController extends Controller
{
    private MapService $mapService;
    private SubjService $subjService;
    private AddressSubjRepository $addressSubjRepository;
    protected MapRepository $mapRepository;

    public function __construct(MapService $mapService, SubjService $subjService, AddressSubjRepository $addressSubjRepository, MapRepository $mapRepository)
    {
        $this->mapService = $mapService;
        $this->subjService = $subjService;
        $this->addressSubjRepository = $addressSubjRepository;
        $this->mapRepository = $mapRepository;
    }

    /**
     * @param Request $request
     * @return Application|Factory|View|null
     */
    public function show(Request $request): Application|Factory|View|null
    {
        try {
            $subjId = $request->id;

            if (!$subjId) {
                Log::channel('error_file')->error('Missing subject ID in request');
                abort(400, 'Subject ID is required');
            }

            // Выносим логику получения карты в репозиторий
            $map = $this->addressSubjRepository->findBySubjId($subjId);

            if (!$map) {
                Log::channel('error_file')->error(
                    'Address not found for subject ID: ' . $subjId
                );
                abort(404, 'Address not found');
            }

            $map['data_subj'] = $this->subjService->findById($subjId);

            if (!$map['data_subj']) {
                Log::channel('error_file')->error(
                    'Subject not found for ID: ' . $subjId
                );
                abort(404, 'Subject not found');
            }

            return view('map.show', ['map' => $map]);
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Error in MapController@show: ' . $e->getMessage() .
                ' | Subj ID: ' . ($subjId ?? 'unknown')
            );
            abort(500, 'Internal server error');
        }
    }


    /**
     * @return Application|Factory|View
     */
    public function index(): Application|Factory|View
    {
        try {
            // Получаем данные из репозитория
            $groups = $this->mapRepository->findMap();

            return view('map.index', ['groups' => $groups]);
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Error in MapController@index: ' . $e->getMessage(),
                ['trace' => $e->getTrace()]
            );
            abort(500, 'Internal server error');
        }
    }

    /**
     * @param Request $request
     * @return Application|Factory|JsonResponse|RedirectResponse
     */
    public function create(Request $request): Application|Factory|JsonResponse|RedirectResponse
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return redirect()->route('login')->with('error', 'Для доступа необходимо авторизоваться');
            }

            $subjId = (int)$request->id;

            // Загружаем Subj с связанным Obj для проверки прав
            $subj = Subj::with('obj')->find($subjId);

            if (!$subj) {
                return response()->json(['answer' => 'error', 'message' => 'Объект не найден'], 404);
            }

            // Проверка прав: админ ИЛИ владелец связанного Obj
            $isOwner = $user->isAdmin() || ($subj->obj && $subj->obj->user_id === $user->id);
            if (!$isOwner) {
                Log::channel('error_file')->error('Unauthorized create attempt', [
                    'user_id' => $user->id,
                    'subj_id' => $subjId,
                    'obj_owner_id' => $subj->obj?->user_id
                ]);
                return redirect()->back()->with('error', 'У вас нет прав для создания карты для этого объекта');
            }

            return view('map.create', ['subj' => $subj]);
        } catch (\Exception $e) {
            Log::channel('error_file')->error('Error in create method', [
                'exception' => $e->getMessage(),
                'subj_id' => $request->id,
                'user_id' => auth()->id()
            ]);
            return redirect()->back()->with('error', 'Произошла ошибка при загрузке формы создания');
        }
    }


    /**
     * @param Request $request
     * @return Application|Factory|View|JsonResponse|RedirectResponse
     *
     */
    public function edit(Request $request): Application|Factory|View|JsonResponse|RedirectResponse
    {
        try {
            $user = auth()->user();
            $subjId = $request->id;

            // Загружаем Subj с связанным Obj для проверки прав
            $subj = Subj::with('obj')->find($subjId);

            if (!$subj) {
                return response()->json(['answer' => 'error', 'message' => 'Объект не найден'], 404);
            }

            // Проверка прав: админ ИЛИ владелец связанного Obj
            $isOwner = $user->isAdmin() || ($subj->obj && $subj->obj->user_id === $user->id);
            if (!$isOwner) {
                Log::channel('error_file')->error('Unauthorized edit attempt', [
                    'user_id' => $user->id,
                    'subj_id' => $subjId,
                    'obj_owner_id' => $subj->obj?->user_id
                ]);
                return redirect()->back()->with('error', 'У вас нет прав для редактирования этого объекта');
            }

            $map = AddressSubj::where('subj_id', $subjId)->first();

            if ($map) {
                return view('map.edit', ['map' => $map]);
            } else {
                return redirect()->route('map.create', ['id' => $subjId]);
            }
        } catch (\Exception $e) {
            Log::channel('error_file')->error('Error in edit method', [
                'exception' => $e->getMessage(),
                'subj_id' => $request->id,
                'user_id' => auth()->id()
            ]);
            return redirect()->back()->with('error', 'Произошла ошибка при загрузке формы редактирования');
        }
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function addSubjectToMap(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Неавторизованный доступ'
                ], 401);
            }

            // Валидация входных данных
            $validated = $request->validate([
                'city_id' => 'required|exists:cities,id',
                'district_id' => 'required|exists:districts,id',
                'street' => 'required|string|max:255',
                'houseNumber' => 'required|string|max:50',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'subj_id' => 'required|integer|exists:subjs,id',
                'obj_id' => 'required|integer'
            ]);

            $result = $this->mapService->addSubjectToMap(
                $validated,
                $user->id,
                $user->isAdmin()
            );

            return response()->json($result, $result['code'] ?? 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::channel('error_file')->error('Unexpected error in controller addSubjectToMap', [
                'exception' => $e->getMessage(),
                'request' => $request->all(),
                'user_id' => auth()->id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Произошла непредвиденная ошибка'
            ], 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Неавторизованный доступ'
                ], 401);
            }

            $subjId = (int)$request->id;

            // Загружаем AddressSubj с связанными моделями для проверки прав
            $addressSubjs = AddressSubj::with(['subj.obj'])->where('subj_id', $subjId)->get();

            if ($addressSubjs->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Адреса для удаления не найдены'
                ], 404);
            }

            // Проверяем права для первого найденного адреса (предполагаем, что все связаны с одним subj)
            $firstAddress = $addressSubjs->first();
            $subj = $firstAddress->subj;

            // Проверка прав: админ ИЛИ владелец связанного Obj
            $isOwner = $user->isAdmin() || ($subj->obj && $subj->obj->user_id === $user->id);
            if (!$isOwner) {
                Log::channel('error_file')->error('Unauthorized destroy attempt', [
                    'user_id' => $user->id,
                    'subj_id' => $subjId,
                    'obj_owner_id' => $subj->obj?->user_id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'У вас нет прав для удаления этого адреса'
                ], 403);
            }

            DB::beginTransaction();

            try {
                if (count($addressSubjs) == 1) {
                    // Удаляем адрес и группу (если это последняя запись в группе)
                    $addressSubjs[0]->delete();

                    // Проверяем, есть ли другие адреса в этой группе
                    $remainingInGroup = AddressSubj::where('group_id', $addressSubjs[0]->group_id)->count();
                    if ($remainingInGroup == 0) {
                        GroupAddressObj::where('id', $addressSubjs[0]->group_id)->delete();
                    }
                } else {
                    // Удаляем только первый найденный адрес
                    $addressSubjs[0]->delete();
                }

                DB::commit();
                return response()->json(['success' => true, 'message' => 'Адрес удалён']);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::channel('error_file')->error('Error during destroy operation', [
                    'exception' => $e->getMessage(),
                    'subj_id' => $subjId,
                    'user_id' => $user->id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка при удалении адреса'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::channel('error_file')->error('Unexpected error in destroy method', [
                'exception' => $e->getMessage(),
                'request' => $request->all(),
                'user_id' => auth()->id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Произошла непредвиденная ошибка'
            ], 500);
        }
    }

}

