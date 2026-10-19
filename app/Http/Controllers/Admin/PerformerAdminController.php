<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Performer;
use App\Services\PerformerService;

class PerformerAdminController extends Controller
{
    private PerformerService $service;

    /**
     * @param PerformerService $service
     */
    public function __construct(PerformerService $service)
    {
        $this->service = $service;
    }



    public function approve(Performer $performer)
    {
        $this->service->publish($performer);

        return back()->with('success', 'Опубликован');
    }

    public function reject(Performer $performer)
    {
        $this->service->unpublish($performer);

        return back()->with('success', 'Снят с публикации');
    }
}
