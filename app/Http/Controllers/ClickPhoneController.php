<?php

namespace App\Http\Controllers;

use App\Services\ClickPhoneService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ClickPhoneController extends Controller
{
    private ClickPhoneService $clickPhoneService;

    public function __construct(ClickPhoneService $clickPhoneService)
    {
        $this->clickPhoneService = $clickPhoneService;
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $subjId = (int) $request->route('id');

        if ($subjId <= 0) {
            Log::channel('error_file')->error('ClickPhone: некорректный subj_id', [
                'id' => $request->route('id'),
            ]);

            return response()->json(['ok' => false], 422);
        }

        try {
            $recorded = $this->clickPhoneService->recordClick($subjId, $request->ip());

            return response()->json(['ok' => $recorded]);
        } catch (\Throwable $e) {
            Log::channel('error_file')->error('ClickPhone: ошибка записи клика', [
                'subj_id' => $subjId,
                'ip'      => $request->ip(),
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json(['ok' => false], 500);
        }
    }
}
