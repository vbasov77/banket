<?php


namespace App\Repositories;


use App\Models\ImgObj;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class ImgObjRepository extends Repository
{
    /**
     * @param int $id
     * @return mixed
     * @throws \Exception
     */
    public function findImgByObjId(int $id): mixed
    {
        try {
            return ImgObj::where('obj_id', $id)->first();
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в ImgObjRepository@findImgByObjId: ' . $e->getMessage(),
                [
                    'sql_query' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'obj_id' => $id
                ]
            );
            throw $e;
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Ошибка в ImgObjRepository@findImgByObjId: ' . $e->getMessage(),
                [
                    'obj_id' => $id,
                    'exception_class' => get_class($e)
                ]
            );
            throw $e;
        }
    }

    /**
     * @param array $data
     * @return int
     * @throws \Exception
     */
    public function insertImgData(array $data): int
    {
        try {
            return ImgObj::insertGetId($data);
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в ImgObjRepository@insertImgData: ' . $e->getMessage(),
                [
                    'sql_query' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'data' => $data,
                    'exception_class' => get_class($e)
                ]
            );
            throw $e;
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Ошибка в ImgObjRepository@insertImgData: ' . $e->getMessage(),
                [
                    'data' => $data,
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString()
                ]
            );
            throw $e;
        }
    }

}