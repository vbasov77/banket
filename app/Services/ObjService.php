<?php


namespace App\Services;

use App\Models\Obj;
use App\Repositories\ObjRepository;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Database\QueryException;
use RuntimeException;


class ObjService extends Service
{
    protected ObjRepository $objRepository;

    /**
     * @param ObjRepository $objRepository
     */
    public function __construct(ObjRepository $objRepository)
    {
        $this->objRepository = $objRepository;
    }

    /**
     * @param array $data
     * @return Obj
     * @throws \Exception
     */
    public function store(array $data): Obj
    {
        try {
            $obj = Obj::create($data);

            if (!$obj) {
                throw new RuntimeException('Не удалось создать объект в базе данных');
            }

            return $obj;
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка при создании объекта: ' . $e->getMessage(),
                [
                    'sql_query' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'insert_data' => $data
                ]
            );
            throw new RuntimeException('Ошибка базы данных при создании объекта', 0, $e);
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Ошибка при создании объекта в ObjService@store: ' . $e->getMessage(),
                [
                    'input_data' => $data,
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString()
                ]
            );
            throw $e;
        }
    }

    /**
     * @return Obj|null
     * @throws \Exception
     */
    public function findObjByUserId(): ?Obj
    {
        try {
            if (!Auth::check()) {
                Log::channel('error_file')->warning('User not authenticated in ObjService@findObjByUserId');
                return null;
            }

            $obj = $this->objRepository->findObjByUserId();

            if (!$obj) {
                Log::channel('error_file')->info('No obj found for user', [
                    'user_id' => Auth::id() ?? 'unknown',
                ]);
            }

            return $obj;
        } catch (QueryException $e) {
            Log::channel('error_file')->error('Database error in ObjService@findObjByUserId', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'user_id' => Auth::id() ?? 'unknown',
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::channel('error_file')->critical('Unexpected error in ObjService@findObjByUserId', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => Auth::id() ?? 'unknown',
            ]);
            throw $e;
        }
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function findById(int $id): mixed
    {
        return $this->objRepository->findById($id);
    }


    /**
     * @param array $array
     * @param int $id
     * @return void
     */
    public function update(array $array, int $id): void
    {
        $this->objRepository->update($array, $id);
    }


    /**
     * @return LengthAwarePaginator
     * @throws \Exception
     */
    public function findObjsWithDetails(): LengthAwarePaginator
    {
        try {
            return $this->objRepository->findObjsWithDetails();
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в ObjService@findObjsWithDetails: ' . $e->getMessage(),
                [
                    'sql_query' => $e->getSql(),
                    'bindings' => $e->getBindings()
                ]
            );
            throw $e;
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Ошибка в ObjService@findObjsWithDetails: ' . $e->getMessage(),
                [
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString()
                ]
            );
            throw $e;
        }
    }

    /**
     * @param array $array
     * @return array
     */
    public function findObjArr(array $array): array
    {
        $objArr = [];
        $count = count($array);
        for ($i = 0; $i < $count; $i++) {
            $objArr[$i] = $array[$i];
            $objArr[$i]->subjs = DB::table('subjs')->where('obj_id', $array[$i]->id)->get();
            $countSubj = count($objArr[$i]->subjs);
            for ($j = 0; $j < $countSubj; $j++) {
                $objArr[$i]->subjs[$j]->path = DB::table('img_subj')->where('subj_id', $objArr[$i]->subjs[$j]->id)->pluck('path');
            }
        }

        return $objArr;
    }

    /**
     * @return int|null
     * @throws AuthenticationException
     */
    public function findIdObjByUserId(): ?int
    {
        try {
            // Проверка авторизации пользователя
            if (!auth()->check()) {
                throw new AuthenticationException('Пользователь не авторизован');
            }

            $objId = $this->objRepository->findIdObjByUserId();

            if ($objId === null) {
                Log::channel('error_file')->error(
                    'Объект не найден для пользователя',
                    [
                        'user_id' => auth()->id()
                    ]
                );
                // Это не ошибка — просто объект не найден, возвращаем null
            }

            return $objId;
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в ObjService@findIdObjByUserId: ' . $e->getMessage(),
                [
                    'sql_query' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'user_id' => auth()->id()
                ]
            );
            throw new \RuntimeException('Ошибка базы данных при поиске объекта', 0, $e);
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Ошибка в ObjService@findIdObjByUserId: ' . $e->getMessage(),
                [
                    'user_id' => auth()->id(),
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString()
                ]
            );
            throw $e;
        }
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function findByIdOnlyObj(int $id): mixed
    {
        return $this->objRepository->findByIdOnlyObj($id);
    }
}

