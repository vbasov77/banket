<?php


namespace App\Services;


use App\Models\Ip;
use App\Repositories\IpRepository;
use Illuminate\Support\Carbon;


class IpService extends Service
{
    private $ipRepository;


    public function __construct()
    {
        $this->ipRepository = new IpRepository();
    }


    /**
     * @param string $ip
     * @return bool
     */
    public function checkIp(string $ip): bool
    {
        return Ip::where('ip', $ip)->whereDate('created_at', Carbon::today())->exists();
    }

    /**
     * @param string $ip
     * @return void
     */
    public function store(string $ip): void
    {
        $this->ipRepository->store($ip);
    }

}