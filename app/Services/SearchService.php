<?php


namespace App\Services;

use App\Repositories\SearchRepository;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class SearchService extends Service
{
    private SearchRepository $searchRepository;

    public function __construct(SearchRepository $searchRepository)
    {
        $this->searchRepository = $searchRepository;
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

}

