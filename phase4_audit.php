<?php
/**
 * Phase 4 — Targeted Accuracy Fix Script
 * Corrects inaccurate implementation details across docs/01-Architecture.
 * Based on actual source code audit of the Finzo codebase.
 */

$baseDir = __DIR__ . '/docs/01-Architecture';
$date = date('Y-m-d');

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseDir));
$fixed = 0;

foreach ($files as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'md') continue;

    $content = file_get_contents($file->getPathname());
    $original = $content;
    
    // -----------------------------------------------
    // FIX 1: Rate Limiting — File-based, NOT Redis
    // -----------------------------------------------
    $content = str_replace(
        'stored this UUID in Redis for 24 hours',
        'tracked via a file-based atomic lock in storage/framework/cache/rate_limits.json using flock()'
    , $content);
    $content = str_replace(
        'Atomic Locks in Redis',
        'File-based Atomic Locks (flock() on rate_limits.json)'
    , $content);
    $content = str_replace(
        'via Atomic Locks in Redis. When the first request hits, an atomic lock',
        'via file-based flock() on rate_limits.json. When the first request hits, a file lock'
    , $content);

    // -----------------------------------------------
    // FIX 2: Kubernetes/EKS — Not used (XAMPP/Apache)
    // -----------------------------------------------
    $content = str_replace(
        'EKS Node 1',
        'PHP-FPM Worker 1'
    , $content);
    $content = str_replace(
        'EKS Node 2',
        'PHP-FPM Worker 2'
    , $content);
    $content = str_replace(
        'Registry->>K8s: Trigger Rolling Update',
        'CI->>Server: Deploy via SSH (ops/deploy.sh)'
    , $content);
    $content = str_replace(
        'Horizontal Pod Autoscaling (HPA)',
        'Process-Level Autoscaling (PHP-FPM pm.dynamic)'
    , $content);
    $content = str_replace(
        'The production cluster spans 3 Availability Zones.',
        'Finzo is designed for deployment on XAMPP/Apache (development) and Apache/Nginx (production) environments.'
    , $content);

    // -----------------------------------------------
    // FIX 3: Session SameSite — Lax in config, Strict in SecurityMiddleware  
    // -----------------------------------------------
    $content = str_replace(
        'SameSite=Strict', 'SameSite=Strict (via SecurityMiddleware) / SameSite=Lax (global config.php fallback)'
    , $content);

    // -----------------------------------------------
    // FIX 4: Auth roles — exact from Gate.php
    // -----------------------------------------------
    $content = str_replace(
        "into `Customer`, `Staff`, and `Admin`",
        "into `customer`, `staff`, `admin`, `manager`, `auditor`, and `super_admin`"
    , $content);
    $content = str_replace(
        'Customer, Staff, and Admin',
        'customer, staff, admin, manager, auditor, and super_admin'
    , $content);

    // -----------------------------------------------
    // FIX 5: Session timeout — 30 min from Auth.php
    // -----------------------------------------------
    $content = str_replace(
        'SESSION_LIFETIME = 86400 (24 hours)',
        'SESSION_LIFETIME = 86400 (24 hours global config), AUTH timeout = 1800 seconds (30 minutes via Auth::requireAuth())'
    , $content);

    // -----------------------------------------------
    // FIX 6: Events system — actual events
    // -----------------------------------------------
    $content = str_replace(
        'Finzo implements a basic event system',
        'Finzo implements a structured event system with domain events including: AuditLogEvent, TransactionCompleted, TransferCompleted, KYCApproved, UserLoggedIn, PasswordChanged, LoanApproved'
    , $content);

    // -----------------------------------------------
    // FIX 7: Strangler Fig pattern — correct router description
    // -----------------------------------------------
    $content = str_replace(
        'Routes are mapped to their Middleware definitions',
        'Routes are mapped via the custom Router class which executes SecurityMiddleware globally, then route-specific middleware. A Strangler Fig fallback serves legacy Controllers_backup files during the incremental refactor'
    , $content);

    // -----------------------------------------------
    // FIX 8: Version bump to 4.0.0
    // -----------------------------------------------
    $content = preg_replace('/version: "3\.\d\.\d"/', 'version: "4.0.0"', $content);
    
    // -----------------------------------------------
    // FIX 9: Update Change Log
    // -----------------------------------------------
    if (strpos($content, 'Phase 4') === false) {
        $content = preg_replace(
            '/(\| Version \| Date \| Author \| Description \|\n\|---\|---\|---\|---\|\n)/',
            "$1| 4.0.0 | {$date} | Architecture Board | Phase 4 Technical Audit: Accuracy corrections based on source code review |\n",
            $content
        );
    }

    // -----------------------------------------------
    // FIX 10: Fix broken cross-references from old paths
    // -----------------------------------------------
    $content = str_replace('../Security-Architecture.md', '../08-Security/README.md', $content);
    $content = str_replace('../API-Architecture.md', '../06-API/README.md', $content);
    $content = str_replace('../Database-Architecture.md', '../05-Database/README.md', $content);
    $content = str_replace('../Backend-Architecture.md', '../04-Backend/README.md', $content);
    $content = str_replace('../Frontend-Architecture.md', '../03-Frontend/README.md', $content);
    $content = str_replace('../Infrastructure-Architecture.md', '../07-Infrastructure/README.md', $content);
    $content = str_replace('../Deployment-Architecture.md', '../09-Deployment/README.md', $content);
    $content = str_replace('../System-Architecture.md', '../01-System/README.md', $content);
    $content = str_replace('../Software-Architecture.md', '../02-Software/README.md', $content);

    if ($content !== $original) {
        file_put_contents($file->getPathname(), $content);
        $fixed++;
    }
}

echo "Phase 4 accuracy pass complete. Files corrected: {$fixed}\n";
