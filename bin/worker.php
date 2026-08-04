<?php
/**
 * Finzo Banking System — Background Queue Worker
 * Usage: php bin/worker.php [queue_name]
 */

// If app.php exists, include it (or whatever the main bootstrap file is)
$bootstrap = __DIR__ . '/../vendor/autoload.php';
if (file_exists($bootstrap)) {
    require_once $bootstrap;
}

$queueName = $argv[1] ?? 'default';

echo "Starting Finzo Queue Worker for queue: [{$queueName}]\n";
echo "Waiting for jobs (Press Ctrl+C to stop)...\n";

// In a real enterprise implementation, this would connect to Redis or a Database Queue
// and poll for serialized jobs, execute them, and handle retries/failures.

$running = true;

// Only register signals if pcntl is enabled
if (function_exists('pcntl_async_signals')) {
    pcntl_async_signals(true);
    pcntl_signal(SIGTERM, function() use (&$running) {
        echo "\nGracefully shutting down worker...\n";
        $running = false;
    });
    pcntl_signal(SIGINT, function() use (&$running) {
        echo "\nGracefully shutting down worker...\n";
        $running = false;
    });
}

while ($running) {
    // Poll queue mechanism goes here
    // $job = Queue::pop($queueName);
    // if ($job) { $job->execute(); }
    
    // Sleep to prevent CPU thrashing when idle
    sleep(2);
}

echo "Worker exited safely.\n";
