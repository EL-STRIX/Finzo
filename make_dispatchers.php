<?php
$files = [
    'add-staff.php' => 'addStaff',
    'delete-branch.php' => 'deleteBranch',
    'generate-staff-password.php' => 'generateStaffPassword',
    'get-next-staff-id.php' => 'getNextStaffId',
    'get-staff-detail.php' => 'getStaffDetail',
    'reset-staff-password.php' => 'resetStaffPassword',
    'staff-action.php' => 'staffAction',
    'staff-detail.php' => 'staffDetail',
    'staff.php' => 'getStaffList',
    'suspended-staff-detail.php' => 'suspendedStaffDetail',
    'suspended-staff.php' => 'getSuspendedStaff',
    'upload-staff-photo.php' => 'uploadStaffPhoto',
    'branches.php' => 'getBranches'
];

foreach ($files as $file => $method) {
    $path = __DIR__ . '/app/Controllers/admin/' . $file;
    $content = "<?php\n"
             . "require_once dirname(__DIR__, 3) . '/bootstrap/app.php';\n"
             . "require_once dirname(__DIR__, 3) . '/middleware/auth-admin.php';\n\n"
             . "use App\Controllers\Admin\AdminStaffBranchController;\n"
             . "use App\Services\AdminStaffBranchService;\n"
             . "use App\Repositories\AdminStaffBranchRepository;\n\n"
             . "\$controller = new AdminStaffBranchController(new AdminStaffBranchService(new AdminStaffBranchRepository()));\n"
             . "\$controller->{$method}();\n";
    file_put_contents($path, $content);
}
echo "Dispatchers created.\n";
