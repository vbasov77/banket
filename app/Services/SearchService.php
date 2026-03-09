<?php


namespace App\Services;

use App\Repositories\ObjRepository;
use App\Repositories\SearchRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;


class SearchService extends Service
{
    private $searchRepository;

    public function __construct()
    {
        $this->searchRepository = new SearchRepository();
    }

    public function search(Request $request)
    {
        return $this->searchRepository->search($request);
    }
    public function searchResults(Request $request)
    {
        return $this->searchRepository->searchResults($request);
    }


}

