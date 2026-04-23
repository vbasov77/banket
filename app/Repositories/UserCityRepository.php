<?php


namespace App\Repositories;


use App\Models\UserCity;
use Illuminate\Support\Facades\Auth;

class UserCityRepository extends Repository
{

    /**
     * @return object|null
     */
    public function findUserCity(): ?object
    {
        $userId = Auth::user()->id;
        return UserCity::where('userId', $userId)->first();
    }
}