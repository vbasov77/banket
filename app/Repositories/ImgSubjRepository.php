<?php


namespace App\Repositories;


use App\Models\ImgSubj;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImgSubjRepository extends Repository
{
    /**
     * @param int $id
     * @return mixed
     * @throws \Exception
     */
    public function findImgByObjId(int $id): mixed
    {
        try {
            return ImgSubj::where('subj_id', $id)
                ->orderBy('position', 'asc')
                ->get();
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в ImgSubjRepository@findImgByObjId: ' . $e->getMessage(),
                [
                    'sql_query' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'subj_id' => $id,
                    'exception_class' => get_class($e)
                ]
            );
            throw $e;
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Ошибка в ImgSubjRepository@findImgByObjId: ' . $e->getMessage(),
                [
                    'subj_id' => $id,
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString()
                ]
            );
            throw $e;
        }
    }

    /**
     * Метод по изменению позиций фото в фотоальбоме(репозиторий)
     * @param array $orderData
     * @return void
     * @throws \Exception
     */
    public function updateImagePositions(array $orderData): void
    {
        DB::beginTransaction();
        try {
            foreach ($orderData as $index => $id) {
                DB::table('img_ban_subj')
                    ->where('id', $id)
                    ->update(['position' => $index]);
            }

            DB::commit();
        } catch (QueryException $e) {
            DB::rollBack();
            Log::channel('error_file')->error(
                'SQL ошибка в ImgSubjRepository@updateImagePositions: ' . $e->getMessage(),
                [
                    'sql_query' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'processed_count' => $index,
                    'total_count' => count($orderData),
                    'exception_class' => get_class($e)
                ]
            );
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('error_file')->error(
                'Ошибка в ImgSubjRepository@updateImagePositions: ' . $e->getMessage(),
                [
                    'processed_count' => $index ?? 0,
                    'total_count' => count($orderData),
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString()
                ]
            );
            throw $e;
        }
    }

    /**
     * @param int $subjId
     * @return int
     * @throws \Exception
     */
    public function getNextPosition(int $subjId): int
    {
        try {
            return ImgSubj::where('subj_id', $subjId)->count() + 1;
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в ImgSubjRepository@getNextPosition: ' . $e->getMessage(),
                [
                    'sql_query' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'subj_id' => $subjId,
                    'exception_class' => get_class($e)
                ]
            );
            throw $e;
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Ошибка в ImgSubjRepository@getNextPosition: ' . $e->getMessage(),
                [
                    'subj_id' => $subjId,
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString()
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
            return ImgSubj::insertGetId($data);
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в ImgSubjRepository@insertImgData: ' . $e->getMessage(),
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
                'Ошибка в ImgSubjRepository@insertImgData: ' . $e->getMessage(),
                [
                    'data' => $data,
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
     * @throws \Exception
     */
    public function findById(int $id): mixed
    {
        try {
            return ImgSubj::find($id);
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в ImgSubjRepository@findById: ' . $e->getMessage(),
                [
                    'img_subj_id' => $id,
                    'exception_class' => get_class($e)
                ]
            );
            throw $e;
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Ошибка в ImgSubjRepository@findById: ' . $e->getMessage(),
                [
                    'img_subj_id' => $id,
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString()
                ]
            );
            throw $e;
        }
    }

    /**
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public function deleteById(int $id): bool
    {
        try {
            $result = ImgSubj::where('id', $id)->delete();
            return $result > 0;
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в ImgSubjRepository@deleteById: ' . $e->getMessage(),
                [
                    'img_subj_id' => $id,
                    'exception_class' => get_class($e)
                ]
            );
            throw $e;
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Ошибка в ImgSubjRepository@deleteById: ' . $e->getMessage(),
                [
                    'img_subj_id' => $id,
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString()
                ]
            );
            throw $e;
        }
    }}