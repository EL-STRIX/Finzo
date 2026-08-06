<?php

namespace App\Shared\Core\Logging;

class Logger implements LoggerInterface
{
    private static ?Logger $instance = null;
    private string $logPath;

    private function __construct()
    {
        $this->logPath = dirname(__DIR__, 4) . '/storage/logs/';
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0777, true);
        }
    }

    public static function getInstance(): Logger
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function emergency(string $message, array $context = []): void { $this->log('emergency', $message, $context); }
    public function alert(string $message, array $context = []): void { $this->log('alert', $message, $context); }
    public function critical(string $message, array $context = []): void { $this->log('critical', $message, $context); }
    public function error(string $message, array $context = []): void { $this->log('error', $message, $context); }
    public function warning(string $message, array $context = []): void { $this->log('warning', $message, $context); }
    public function notice(string $message, array $context = []): void { $this->log('notice', $message, $context); }
    public function info(string $message, array $context = []): void { $this->log('info', $message, $context); }
    public function debug(string $message, array $context = []): void { $this->log('debug', $message, $context); }

    public function log(string $level, string $message, array $context = []): void
    {
        $date = date('Y-m-d');
        $time = date('Y-m-d H:i:s');
        
        // Remove sensitive info
        $context = $this->maskSensitiveData($context);

        $logEntry = json_encode([
            'timestamp' => $time,
            'level' => strtoupper($level),
            'message' => $message,
            'context' => $context
        ]);

        $file = $this->logPath . "finzo-{$date}.log";
        file_put_contents($file, $logEntry . PHP_EOL, FILE_APPEND);
    }

    private function maskSensitiveData(array $context): array
    {
        $sensitiveKeys = ['password', 'password_confirmation', 'pin', 'txn_pin', 'token', 'csrf_token', 'secret', 'card_number', 'pan', 'cvv'];
        array_walk_recursive($context, function (&$value, $key) use ($sensitiveKeys) {
            if (in_array(strtolower($key), $sensitiveKeys)) {
                $value = '********';
            }
        });
        return $context;
    }
}
