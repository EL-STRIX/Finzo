<?php

namespace App\Application\Jobs;

use App\Shared\Core\Queue\JobInterface;
use App\Shared\Core\Container;
use App\Domain\Contracts\Services\AdminOperationsServiceInterface;

class ExportCsvJob implements JobInterface
{
    private array $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function handle(): void
    {
        $adminId = $this->data['admin_id'] ?? 0;
        $exportType = $this->data['type'] ?? 'transactions';
        $filters = $this->data['filters'] ?? [];
        
        $container = Container::getInstance();
        $service = $container->resolve(AdminOperationsServiceInterface::class);
        
        $filename = 'finzo_' . $exportType . '_' . date('Ymd_His') . '.csv';
        // Need to make sure FINZO_ROOT is defined, or use dirname
        $exportDir = defined('FINZO_ROOT') ? FINZO_ROOT . '/storage/exports' : dirname(__DIR__, 3) . '/storage/exports';
        
        if (!is_dir($exportDir)) {
            mkdir($exportDir, 0755, true);
        }
        
        $filePath = $exportDir . '/' . $filename;
        
        try {
            if ($exportType === 'transactions') {
                $generator = $service->getTransactions($filters, 1, 999999, true);
            } elseif ($exportType === 'users') {
                $generator = $service->exportUsers($filters);
            } else {
                return;
            }
            
            if ($generator) {
                $output = fopen($filePath, 'w');
                $headerWritten = false;
                foreach ($generator as $row) {
                    if (!$headerWritten) {
                        fputcsv($output, array_keys($row));
                        $headerWritten = true;
                    }
                    fputcsv($output, $row);
                }
                fclose($output);
                
                $pdo = \App\Shared\Core\Database::getInstance();
                if (function_exists('send_notification')) {
                    $downloadLink = '/api/v1/admin/download-export?file=' . urlencode($filename);
                    send_notification($pdo, $adminId, \App\Domain\Enums\NotificationTypeEnum::SYSTEM->value, 'Export Complete', "Your {$exportType} CSV export is ready. <a href=\"{$downloadLink}\">Download Here</a>");
                }
            }
        } catch (\Throwable $e) {
            \App\Shared\Core\Logging\Logger::getInstance()->error("ExportCsvJob failed: " . $e->getMessage());
        }
    }
}
