<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ReportController extends Controller
{
    private ReportService $reportService;

    /**
     * ReportController constructor.
     */
    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }


    public function index()
    {
        try {
            $dataWeek = $this->reportService->findIps();
            $week = $this->reportService->findArrayDaysWeek();

            $dataPhone = $this->reportService->findPhoneClicks14Days();
            $days14 = $this->reportService->findArrayDays14();

            return view('reports.index', [
                'week'      => $week,
                'dataWeek'  => $dataWeek,
                'days14'    => $days14,
                'dataPhone' => $dataPhone,
            ]);

        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Ошибка в ReportController@index: ' . $e->getMessage(),
                [
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString(),
                ]
            );

            return view('reports.index', [
                'week'      => [],
                'dataWeek'  => [],
                'days14'    => [],
                'dataPhone' => [],
                'error_message' => 'Не удалось загрузить данные отчёта. Попробуйте позже.',
            ]);
        }
    }

    public function clearDb()
    {
        $count = $this->reportService->clearDb();

        return \view('reports.clear', ['count' => $count]);
    }

    /**
     * @param Request $request
     * @return Factory|View
     */
    public function edit(Request $request)
    {
        return view('reports.edit');
    }

}
