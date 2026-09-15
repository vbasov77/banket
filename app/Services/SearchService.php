<?php


namespace App\Services;

use App\Models\Subj;
use App\Repositories\ObjRepository;
use App\Repositories\SearchRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class SearchService extends Service
{
    private SearchRepository $searchRepository;

    protected ObjRepository $objRepository;

    public function __construct(SearchRepository $searchRepository, ObjRepository $objRepository)
    {
        $this->searchRepository = $searchRepository;
        $this->objRepository = $objRepository;
    }

    /**
     * Выполнить поиск с фильтрами
     *
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function searchResults(Request $request): array
    {
        try {
            return $this->searchRepository->searchResults($request);
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'Database query error in SearchService@searchResults: ' . $e->getMessage(),
                [
                    'trace' => $e->getTrace(),
                    'filters' => $request->all(),
                    'sql' => $e->getSql()
                ]
            );
            throw $e;
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Unexpected error in SearchService@searchResults: ' . $e->getMessage(),
                [
                    'trace' => $e->getTrace(),
                    'filters' => $request->all()
                ]
            );
            throw $e;
        }
    }

    public function getFiltersDataByCity(int $cityId): array
    {
        return $this->searchRepository->getFiltersDataByCity($cityId);
    }

    public function getReadableFilters(): array
    {
        $rawFilters = session('selected_filters', []);

        return $this->searchRepository->getReadableFilters($rawFilters);
    }

    public function searchByName(Request $request): LengthAwarePaginator
    {
        try {
            $query = trim($request->input('q', ''));

            return $this->objRepository->findObjsWithDetails($request, $query !== '' ? $query : null);
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Error in SearchService@searchByName',
                [
                    'message' => $e->getMessage(),
                    'trace'   => $e->getTraceAsString(),
                    'query'   => $query ?? '',
                ]
            );

            // Возвращаем пустой пагинатор вместо падения приложения
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 7);
        }
    }



}

