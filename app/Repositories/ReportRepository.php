<?php


namespace App\Repositories;


use App\Models\Ip;

class ReportRepository extends Repository
{
    public function findIps(object $startDate, object $endDate)
    {
        return Ip::whereBetween('created_at', [$startDate, $endDate])->get();
    }


}