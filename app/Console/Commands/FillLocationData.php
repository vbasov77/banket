<?php

namespace App\Console\Commands;

use App\Models\GroupAddressObj;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FillLocationData extends Command
{
    protected $signature = 'group-address:fill-location';
    protected $description = 'Fill location column with POINT data from latitude/longitude';

    public function handle()
    {
        $this->info('Starting to fill location data...');

        GroupAddressObj::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->chunk(1000, function ($groups) {
                foreach ($groups as $group) {
                    DB::table('group_address_objs')
                        ->where('id', $group->id)
                        ->update([
                            'location' => DB::raw("POINT({$group->longitude}, {$group->latitude})")
                        ]);
                }
                $this->info('Processed chunk of 1000 records');
            });

        $this->info('Location data filled successfully!');
    }
}
