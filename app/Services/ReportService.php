<?php


namespace App\Services;


use App\Models\Ip;
use App\Repositories\ReportRepository;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class ReportService extends Service
{
    private $reportRepository;



    public function __construct()
    {
        $this->reportRepository = new ReportRepository();
    }

    public function findArrayDaysWeek(): string
    {
        $day = 86400;
        $format = 'd.m';
        $startTime = strtotime(date('d.m.Y', strtotime('-6 day')));
        $days = [];
        $weekDays = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];
        for ($i = 0; $i < 7; $i++) {
            $numDay = date('N', ($startTime + ($i * $day))) - 1;
            $days[] = date($format, ($startTime + ($i * $day)))
                . ' ' . date($weekDays[$numDay], ($startTime + ($i * $day)));
        }

        return implode(',', $days);
    }

    /**
     * @return string
     */
    public function findIps(): string
    {
        try {
            $startDate = now()->subDays(7);
            $endDate = now();

            $data = $this->reportRepository->findIps($startDate, $endDate);

            $counts = array_fill(0, 7, 0);

            foreach ($data as $value) {
                $dayOffset = (int)now()->startOfDay()->diffInDays(
                    \Carbon\Carbon::parse($value->created_at)->startOfDay()
                );

                if ($dayOffset >= 0 && $dayOffset <= 6) {
                    $counts[$dayOffset]++;
                }
            }

            return implode(',', array_reverse($counts));

        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в ReportService@findIps: ' . $e->getMessage(),
                [
                    'sql_query' => $e->getSql(),
                    'bindings'  => $e->getBindings(),
                ]
            );
            return '';
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Ошибка в ReportService@findIps: ' . $e->getMessage(),
                [
                    'exception_class' => get_class($e),
                    'trace'           => $e->getTraceAsString(),
                ]
            );
            return '';
        }
    }

    public function clearDb(): int
    {
        try {
            $startDate = now()->subDays(7);

            // 1. Считаем, сколько планируем удалить (опционально, можно убрать, если не нужно логировать план)
            $plannedCount = Ip::where('created_at', '<', $startDate)->count();

            if ($plannedCount === 0) {
                return 0;
            }

            // 2. Массовое удаление ОДНИМ запросом (быстро и надежно)
            $deletedCount = Ip::where('created_at', '<', $startDate)->delete();

            Log::channel('info_file')->info('Очистка IP-логов', [
                'planned' => $plannedCount,
                'actual_deleted' => $deletedCount,
                'cutoff_date' => $startDate->toDateTimeString(),
            ]);

            return $deletedCount;

        } catch (QueryException $e) {
            // Ошибка БД (нет прав, таблица заблокирована и т.д.)
            Log::channel('error_file')->error(
                'SQL ошибка в ReportService@clearDb: ' . $e->getMessage(),
                [
                    'sql_query' => $e->getSql(),
                    'bindings'  => $e->getBindings(),
                    'cutoff_date' => $startDate->toDateTimeString() ?? 'unknown',
                ]
            );
            // Возвращаем 0, чтобы контроллер не упал, но в логах будет причина
            return 0;
        } catch (\Exception $e) {
            // Любая другая ошибка
            Log::channel('error_file')->error(
                'Ошибка в ReportService@clearDb: ' . $e->getMessage(),
                [
                    'exception_class' => get_class($e),
                    'trace'           => $e->getTraceAsString(),
                ]
            );
            return 0;
        }
    }
}