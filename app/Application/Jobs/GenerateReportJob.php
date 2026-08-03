<?php

namespace App\Application\Jobs;

use App\Shared\Core\Queue\JobInterface;
use App\Shared\Core\Container;
use App\Domain\Contracts\Services\ReportServiceInterface;

class GenerateReportJob implements JobInterface
{
    private array $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function handle(): void
    {
        $adminId = $this->data['admin_id'] ?? 0;
        $reportType = $this->data['type'] ?? 'financial';
        $filters = $this->data['filters'] ?? [];
        
        $container = Container::getInstance();
        $service = $container->resolve(ReportServiceInterface::class);
        
        $filename = 'finzo_report_' . $reportType . '_' . date('Ymd_His') . '.json';
        $exportDir = defined('FINZO_ROOT') ? FINZO_ROOT . '/storage/reports' : dirname(__DIR__, 3) . '/storage/reports';
        
        if (!is_dir($exportDir)) {
            mkdir($exportDir, 0755, true);
        }
        
        $filePath = $exportDir . '/' . $filename;
        
        try {
            if ($reportType === 'financial') {
                $reportData = $service->generateFinancialReport($adminId, $filters);
            } elseif ($reportType === 'demographics') {
                $reportData = $service->generateDemographicsReport($adminId, $filters);
            } else {
                return;
            }
            
            file_put_contents($filePath, json_encode($reportData, JSON_PRETTY_PRINT));
            
            $pdo = \App\Shared\Core\Database::getInstance();
            if (function_exists('send_notification')) {
                $downloadLink = '/storage/reports/' . $filename;
                send_notification($pdo, $adminId, \App\Domain\Enums\NotificationTypeEnum::SYSTEM->value, 'Report Generated', "Your {$reportType} report is ready. Path: {$downloadLink}");
            }
        } catch (\Throwable $e) {
            \App\Shared\Core\Logging\Logger::getInstance()->error("GenerateReportJob failed: " . $e->getMessage());
        }
    }
}
