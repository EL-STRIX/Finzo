<?php
// do_append.php
$files = [
    'add-staff' => 'addStaff',
    'get-staff-detail' => 'getStaffDetail',
    'staff-detail' => 'staffDetail',
    'staff' => 'getStaffList',
    'suspended-staff-detail' => 'suspendedStaffDetail',
    'suspended-staff' => 'getSuspendedStaff',
    'branches' => 'getBranches'
];

$adminDir = __DIR__ . '/app/Controllers/admin/';
$controllerPath = __DIR__ . '/app/Controllers/Admin/AdminStaffBranchController.php';
$controllerCode = file_get_contents($controllerPath);
$controllerCode = rtrim($controllerCode, "}\n\r "); // remove last brace

foreach ($files as $file => $method) {
    $filePath = $adminDir . $file . '.php';
    if (!file_exists($filePath)) continue;
    
    $content = file_get_contents($filePath);
    $content = preg_replace('/<\?php\s*/', '', $content);
    $content = preg_replace('/require_once.+;\s*/', '', $content);
    $content = preg_replace('/header\(.+\);\s*/', '', $content);
    $content = str_replace('<?php', '', $content);
    $content = str_replace('?>', '', $content);

    $methodBody = "\n    public function {$method}() {\n        \$pdo = \$this->service->getRepo()->getPDO();\n" . $content . "\n    }\n";
    $controllerCode .= $methodBody;

    // Create dispatcher
    $dispatcher = "<?php\nrequire_once dirname(__DIR__, 3) . '/bootstrap/app.php';\nrequire_once dirname(__DIR__, 2) . '/Middleware/auth-admin.php';\n\nuse App\Controllers\Admin\AdminStaffBranchController;\nuse App\Services\AdminStaffBranchService;\nuse App\Repositories\AdminStaffBranchRepository;\n\n\$controller = new AdminStaffBranchController(new AdminStaffBranchService(new AdminStaffBranchRepository()));\n\$controller->{$method}();\n";
    file_put_contents($filePath, $dispatcher);
}

// Don't forget upload-staff-photo since I wrote it in Part2
$uploadContent = file_get_contents(__DIR__ . '/app/Controllers/admin/upload-staff-photo.php');
if (strpos($uploadContent, 'uploadStaffPhoto()') === false) {
    $dispatcher = "<?php\nrequire_once dirname(__DIR__, 3) . '/bootstrap/app.php';\nrequire_once dirname(__DIR__, 2) . '/Middleware/auth-admin.php';\n\nuse App\Controllers\Admin\AdminStaffBranchControllerPart2;\nuse App\Services\AdminStaffBranchService;\nuse App\Repositories\AdminStaffBranchRepository;\n\n\$controller = new AdminStaffBranchControllerPart2(new AdminStaffBranchService(new AdminStaffBranchRepository()));\n\$controller->uploadStaffPhoto();\n";
    file_put_contents(__DIR__ . '/app/Controllers/admin/upload-staff-photo.php', $dispatcher);
}

// And the ones I already put in AdminStaffBranchController
$alreadyDone = ['add-branch' => 'addBranch', 'delete-branch' => 'deleteBranch', 'generate-staff-password' => 'generateStaffPassword', 'get-next-staff-id' => 'getNextStaffId', 'reset-staff-password' => 'resetStaffPassword', 'staff-action' => 'staffAction'];
foreach ($alreadyDone as $f => $m) {
    $dispatcher = "<?php\nrequire_once dirname(__DIR__, 3) . '/bootstrap/app.php';\nrequire_once dirname(__DIR__, 2) . '/Middleware/auth-admin.php';\n\nuse App\Controllers\Admin\AdminStaffBranchController;\nuse App\Services\AdminStaffBranchService;\nuse App\Repositories\AdminStaffBranchRepository;\n\n\$controller = new AdminStaffBranchController(new AdminStaffBranchService(new AdminStaffBranchRepository()));\n\$controller->{$m}();\n";
    file_put_contents($adminDir . $f . '.php', $dispatcher);
}

$controllerCode .= "\n}\n";
file_put_contents($controllerPath, $controllerCode);

echo json_encode(["status" => "success"]);
