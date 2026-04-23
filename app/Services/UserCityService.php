<?php

namespace App\Services;

use App\Exceptions\CitySearchException;
use App\Models\City;
use App\Models\UserCity;
use App\Repositories\UserCityRepository;
use Doctrine\DBAL\Query\QueryException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class UserCityService extends Service
{
    protected UserCityRepository $userCityRepository;

    /**
     * @param UserCityRepository $userCityRepository
     */
    public function __construct(UserCityRepository $userCityRepository)
    {
        $this->userCityRepository = $userCityRepository;
    }

    /**
     * @return object|null
     */
    public function findUserCity(): ?object
    {
        return $this->userCityRepository->findUserCity();
    }

    /**
     * @param int $id
     * @return string|null
     */
    public function findNameUserCity(int $id): ?string
    {
        return City::where('id', $id)->value('name');
    }

    /**
     * @param Request $request
     * @return void
     */
    public function checkSessionUserCity(Request $request): void
    {
        $sessionUserCity = session('user_city');
        $sessionCityId = session('city_id');

        if (!$sessionUserCity || !$sessionCityId) {
            if (Auth::check()) {
                $userCity = $this->findUserCity();
                if ($userCity) {
                    $idUserCity = $userCity->city_id;
                    Session::put('user_city', $this->findNameUserCity($idUserCity));
                    Session::put('city_id', $idUserCity);
                    $request->session()->save();
                } else {
                    Session::put('user_city', 'Санкт-Петербург');
                    Session::put('city_id', 1);
                    $request->session()->save();
                }
            } else {
                Session::put('user_city', 'Санкт-Петербург');
                Session::put('city_id', 1);
                $request->session()->save();
            }
        }
    }
}
