<?php


namespace App\Repositories;


use App\Models\DetailsObj;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class DetailsObjRepository extends Repository
{
    /**
     * @param int $id
     * @return mixed
     * @throws \Exception
     */
    public function findById(int $id): mixed
    {
        try {
            return DetailsObj::where('id', $id)->first();
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в DetailsObjRepository@findById: ' . $e->getMessage(),
                [
                    'sql_query' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'requested_id' => $id
                ]
            );
            throw $e;
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Ошибка в DetailsObjRepository@findById: ' . $e->getMessage(),
                [
                    'requested_id' => $id,
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString()
                ]
            );
            throw $e;
        }
    }

    /**
     * @param array $data
     * @return void
     * @throws Exception
     */
    public function update(array $data): void
    {
        try {
            $updated = DetailsObj::where('id', $data['id'])->update($data);

            if (!$updated) {
                throw new ModelNotFoundException(
                    "Объект с ID {$data['id']} не найден или не был обновлён"
                );
            }
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в DetailsObjRepository@update: ' . $e->getMessage(),
                [
                    'sql_query' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'update_data' => $data
                ]
            );
            throw $e;
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (Exception $e) {
            Log::channel('error_file')->error(
                'Ошибка в DetailsObjRepository@update: ' . $e->getMessage(),
                [
                    'update_data' => $data,
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString()
                ]
            );
            throw $e;
        }
    }

    /**
     * @param array $data
     * @return mixed
     * @throws Exception
     */
    public function store(array $data): mixed
    {
        try {
            return DetailsObj::create($data);
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в DetailsRepository@store: ' . $e->getMessage(),
                [
                    'sql_query' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'insert_data' => $data
                ]
            );
            throw new \RuntimeException('Ошибка БД при сохранении данных', 0, $e);
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Неожиданная ошибка в DetailsRepository@store: ' . $e->getMessage(),
                [
                    'data_attempted' => $data,
                    'exception_class' => get_class($e)
                ]
            );
            throw $e;
        }
    }

}