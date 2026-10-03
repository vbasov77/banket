<?php


namespace App\Services;


use App\Models\Ip;
use App\Repositories\ClickPhoneRepository;
use App\Repositories\ReportRepository;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class ReportService extends Service
{
    private ReportRepository $reportRepository;
    private ClickPhoneRepository $clickPhoneRepository;

    /**
     * @param ReportRepository $reportRepository
     * @param ClickPhoneRepository $clickPhoneRepository
     */
    public function __construct(ReportRepository $reportRepository, ClickPhoneRepository $clickPhoneRepository)
    {
        $this->reportRepository = $reportRepository;
        $this->clickPhoneRepository = $clickPhoneRepository;
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


    public function findPhoneClicks14Days(): string
    {
        try {
            $startDate = now()->subDays(13)->startOfDay(); // 14 дней: сегодня + 13 назад
            $endDate   = now();

            $rows = $this->clickPhoneRepository->findPhoneClicksByDay($startDate, $endDate);

            $counts = array_fill(0, 14, 0);

            foreach ($rows as $day => $cnt) {
                $dayOffset = (int) now()->startOfDay()->diffInDays(
                    \Carbon\Carbon::parse($day)->startOfDay()
                );

                if ($dayOffset >= 0 && $dayOffset <= 13) {
                    $counts[$dayOffset] = (int) $cnt;
                }
            }

            // реверс, как в findIps: слева — самый старый день
            return implode(',', array_reverse($counts));

        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в ReportService@findPhoneClicks14Days: ' . $e->getMessage(),
                [
                    'sql_query' => $e->getSql(),
                    'bindings'  => $e->getBindings(),
                ]
            );
            return '';
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Ошибка в ReportService@findPhoneClicks14Days: ' . $e->getMessage(),
                [
                    'exception_class' => get_class($e),
                    'trace'           => $e->getTraceAsString(),
                ]
            );
            return '';
        }
    }

    /**
     * Подписи 14 дней в том же формате: 'd.m Пн'.
     */
    public function findArrayDays14(): string
    {
        $format  = 'd.m';
        $daySec  = 86400;
        $weekDays = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];
        $startTime = strtotime(date('d.m.Y', strtotime('-13 day')));

        $days = [];
        for ($i = 0; $i < 14; $i++) {
            $ts = $startTime + ($i * $daySec);
            $days[] = date($format, $ts) . ' ' . $weekDays[date('N', $ts) - 1];
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

            // 2. Массовое удаление ОДНИМ запросом (быстро и надежно)
            $deletedCount = Ip::where('created_at', '<', $startDate)->delete();

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