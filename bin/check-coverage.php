<?php
/**
 * Simple script to parse PHPUnit Clover XML coverage reports and enforce a minimum threshold.
 * Usage: php bin/check-coverage.php <path/to/coverage.xml> <threshold>
 */

if ($argc !== 3) {
    echo "Usage: php check-coverage.php <coverage.xml> <threshold_percentage>\n";
    exit(1);
}

$inputFile = $argv[1];
$threshold = (float)$argv[2];

if (!file_exists($inputFile)) {
    echo "Error: Coverage file '{$inputFile}' not found.\n";
    exit(1);
}

$xml = simplexml_load_file($inputFile);
if (!$xml) {
    echo "Error: Failed to parse XML coverage file.\n";
    exit(1);
}

// Find the global metrics tag under project -> metrics
$metrics = $xml->xpath('//project/metrics');
if (empty($metrics)) {
    echo "Error: Could not find project metrics in coverage file.\n";
    exit(1);
}

$totalStatements = (int)$metrics[0]['statements'];
$coveredStatements = (int)$metrics[0]['coveredstatements'];

if ($totalStatements === 0) {
    echo "Warning: Project has 0 statements. Bypassing coverage check.\n";
    exit(0);
}

$coverage = ($coveredStatements / $totalStatements) * 100;
$coverageFormatted = number_format($coverage, 2);

echo "Total Statements: {$totalStatements}\n";
echo "Covered Statements: {$coveredStatements}\n";
echo "Line Coverage: {$coverageFormatted}%\n";
echo "Threshold: {$threshold}%\n";

if ($coverage < $threshold) {
    echo "❌ Coverage check failed! Coverage is below the minimum threshold.\n";
    exit(1);
}

echo "✅ Coverage check passed!\n";
exit(0);
