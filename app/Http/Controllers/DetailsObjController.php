<?php

namespace App\Http\Controllers;

use App\Http\Requests\DetailsObj\CreateDetailsObjRequest;
use App\Http\Requests\DetailsObj\EditDetailsObjRequest;
use App\Services\DetailsObjService;
use App\Services\ObjService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class DetailsObjController extends Controller
{
    private ObjService $objService;

    private DetailsObjService $detailsObjService;


    public function __construct(ObjService $objService, DetailsObjService $detailsObjService)
    {
        $this->objService = $objService;

        $this->detailsObjService = $detailsObjService;
    }

    /**
     * @return View
     */
    public function create(): View
    {
        try {
            $obj = $this->objService->findObjByUserId();
            return view('details_obj.create', ['obj' => $obj]);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::channel('error_file')->error('Database error in ObjController@create', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
            abort(500, 'Произошла ошибка при загрузке данных. Пожалуйста, попробуйте позже.');
        } catch (\Exception $e) {
            Log::channel('error_file')->critical('Unexpected error in ObjController@create', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            abort(500, 'Произошла внутренняя ошибка. Пожалуйста, попробуйте позже.');
        }
    }

    /**
     * @param CreateDetailsObjRequest $request
     * @return RedirectResponse
     */
    public function store(CreateDetailsObjRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();
            $this->detailsObjService->store($data);

            return redirect()->route('create.subj');
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Ошибка в DetailsObjController@store: ' . $e->getMessage(),
                [
                    'request_data' => $request->all(),
                    'exception_code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]
            );

            // Возвращаем ответ с ошибкой
            return redirect()
                ->back()
                ->withErrors(['error' => 'Произошла ошибка при сохранении данных'])
                ->withInput();
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function edit(Request $request): View
    {
        try {
            $obj = $this->detailsObjService->findById($request->id);

            $alcoholData = null;
            $moreData = null;

            if ($obj && $obj->alcohol) {
                $alcoholData = $this->parseJsonValue($obj->alcohol);
            }

            if ($obj && $obj->more) {
                $moreData = $this->parseJsonValue($obj->more);
            }

            return view('details_obj.edit', [
                'obj' => $obj,
                'alcoholValue' => $alcoholData['value'] ?? null,
                'alcoholPrice' => $alcoholData['price'] ?? null,
                'moreValue' => $moreData['value'] ?? null,
                'morePrice' => $moreData['price'] ?? null
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::channel('error_file')->error(
                'Объект не найден в DetailsObjController@edit: ID ' . $request->id,
                [
                    'requested_id' => $request->id,
                    'user_id' => auth()->id()
                ]
            );
            abort(404, 'Объект не найден');
        } catch (\InvalidArgumentException $e) {
            Log::channel('error_file')->error(
                'Ошибка парсинга данных в DetailsObjController@edit: ' . $e->getMessage(),
                [
                    'input_data' => [
                        'alcohol' => $obj->alcohol ?? null,
                        'more' => $obj->more ?? null
                    ],
                    'obj_id' => $obj?->id ?? null,
                    'user_id' => auth()->id()
                ]
            );

            return view('details_obj.edit', [
                'obj' => $obj ?? null,
                'alcoholValue' => null,
                'alcoholPrice' => null,
                'moreValue' => null,
                'morePrice' => null,
                'error' => 'Ошибка при обработке данных объекта'
            ]);
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Неожиданная ошибка в DetailsObjController@edit: ' . $e->getMessage(),
                [
                    'input_data' => $request->all(),
                    'user_id' => auth()->id(),
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString()
                ]
            );

            return view('details_obj.edit', [
                'obj' => $obj ?? null,
                'alcoholValue' => null,
                'alcoholPrice' => null,
                'moreValue' => null,
                'morePrice' => null,
                'error' => 'Произошла внутренняя ошибка сервера'
            ]);
        }
    }

    /**
     * @param string $jsonValue
     * @return array
     */
    private function parseJsonValue(string $jsonValue): array
    {
        try {
            if (empty($jsonValue)) {
                return ['value' => '', 'price' => 0];
            }

            if (str_contains($jsonValue, ':')) {
                $parts = explode(':', $jsonValue);
                if (count($parts) !== 2) {
                    throw new \InvalidArgumentException('Некорректный формат данных: ожидается "значение:цена"');
                }
                return [
                    'value' => trim($parts[0]),
                    'price' => is_numeric($parts[1]) ? (float)$parts[1] : 0
                ];
            }

            return ['value' => $jsonValue, 'price' => 0];
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Ошибка в parseJsonValue: ' . $e->getMessage(),
                [
                    'json_value' => $jsonValue,
                    'user_id' => auth()->id()
                ]
            );
            throw new \InvalidArgumentException('Ошибка при парсинге данных: ' . $e->getMessage());
        }
    }


    /**
     * @param EditDetailsObjRequest $request
     * @return RedirectResponse
     */
    public function update(EditDetailsObjRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();
            $this->detailsObjService->update($data);
            return redirect()->route('my.obj');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::channel('error_file')->error(
                'Ошибка валидации в DetailsObjController@update: ' . $e->getMessage(),
                [
                    'validation_errors' => $e->errors(),
                    'input_data' => $request->all(),
                    'user_id' => auth()->id()
                ]
            );
            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\InvalidArgumentException $e) {
            Log::channel('error_file')->error(
                'Ошибка данных в DetailsObjController@update: ' . $e->getMessage(),
                [
                    'input_data' => $data ?? $request->all(),
                    'user_id' => auth()->id()
                ]
            );
            return redirect()
                ->back()
                ->with('error', 'Некорректные данные для обновления')
                ->withInput();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::channel('error_file')->error(
                'Объект не найден в DetailsObjController@update: ID ' . ($data['id'] ?? 'unknown'),
                [
                    'requested_id' => $data['id'] ?? null,
                    'user_id' => auth()->id()
                ]
            );
            return redirect()
                ->route('my.obj')
                ->with('error', 'Объект не найден');
        } catch (\Illuminate\Database\QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в DetailsObjController@update: ' . $e->getMessage(),
                [
                    'sql_query' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'input_data' => $data ?? $request->all(),
                    'user_id' => auth()->id()
                ]
            );
            return redirect()
                ->back()
                ->with('error', 'Ошибка при сохранении данных в базе данных')
                ->withInput();
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Неожиданная ошибка в DetailsObjController@update: ' . $e->getMessage(),
                [
                    'input_data' => $data ?? $request->all(),
                    'user_id' => auth()->id(),
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString()
                ]
            );
            return redirect()
                ->route('my.obj')
                ->with('error', 'Произошла внутренняя ошибка сервера');
        }
    }

}
