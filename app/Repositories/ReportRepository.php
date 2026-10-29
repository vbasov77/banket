<?php


namespace App\Repositories;


use App\Models\Ip;

class ReportRepository extends Repository
{
    /**
     * @param object $startDate
     * @param object $endDate
     * @return mixed
     */
    public function findIps(object $startDate, object $endDate): mixed
    {
        return Ip::whereBetween('created_at', [$startDate, $endDate])->get();
    }


}