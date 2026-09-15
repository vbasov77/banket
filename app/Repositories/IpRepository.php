<?php

declare(strict_types=1);

namespace App\Repositories;


use App\Models\Ip;
use Illuminate\Support\Facades\DB;

class IpRepository extends Repository
{
    /**
     * @param string $ip
     * @return void
     */    public function store(string $ip): void
    {
        Ip::insert(['ip' => $ip]);
    }
}