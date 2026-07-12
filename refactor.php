<?php
// refactor.php
$files = [
    'add-staff.php', 'delete-branch.php', 'generate-staff-password.php',
    'get-next-staff-id.php', 'get-staff-detail.php', 'reset-staff-password.php',
    'staff-action.php', 'staff-detail.php', 'staff.php', 'suspended-staff-detail.php',
    'suspended-staff.php', 'upload-staff-photo.php', 'add-branch.php', 'branches.php'
];

$adminDir = __DIR__ . '/app/Controllers/admin/';
$controllerPath = __DIR__ . '/app/Controllers/Admin/AdminStaffBranchController.php';
$servicePath = __DIR__ . '/app/Services/AdminStaffBranchService.php';
$repoPath = __DIR__ . '/app/Repositories/AdminStaffBranchRepository.php';

$controllerCode = "<?php\nnamespace App\Controllers\Admin;\n\nuse App\Services\AdminStaffBranchService;\nuse Exception;\nuse PDOException;\nuse PDO;\nuse DateTime;\n\nclass AdminStaffBranchController {\n    protected AdminStaffBranchService \$service;\n\n    public function __construct(AdminStaffBranchService \$service) {\n        \$this->service = \$service;\n    }\n";

$serviceCode = "<?php\nnamespace App\Services;\n\nuse App\Repositories\AdminStaffBranchRepository;\nuse Exception;\nuse DateTime;\nuse PDOException;\nuse PDO;\n\nclass AdminStaffBranchService {\n    protected AdminStaffBranchRepository \$repo;\n\n    public function __construct(AdminStaffBranchRepository \$repo) {\n        \$this->repo = \$repo;\n    }\n\n    public function getRepo(): AdminStaffBranchRepository {\n        return \$this->repo;\n    }\n";

$repoCode = "<?php\nnamespace App\Repositories;\n\nuse PDO;\n\nclass AdminStaffBranchRepository extends BaseRepository {\n    public function getPDO(): PDO {\n        return \$this->db;\n    }\n    public function beginTransaction() {\n        return \$this->db->beginTransaction();\n    }\n    public function commit() {\n        return \$this->db->commit();\n    }\n    public function rollBack() {\n        return \$this->db->rollBack();\n    }\n    public function inTransaction() {\n        return \$this->db->inTransaction();\n    }\n}\n";

foreach ($files as $file) {
    $filePath = $adminDir . $file;
    if (!file_exists($filePath)) continue;
    $content = file_get_contents($filePath);
    
    // Extract method name
    $methodName = str_replace('.php', '', $file);
    $methodNameParts = explode('-', $methodName);
    $camelMethod = $methodNameParts[0];
    for ($i = 1; $i < count($methodNameParts); $i++) {
        $camelMethod .= ucfirst($methodNameParts[$i]);
    }
    // Handle 'staff' to 'getStaffList', 'branches' to 'getBranches' if needed, but let's just use camelCase: 'staff' => 'staff', 'branches' => 'branches'

    // Remove <?php and requires
    $content = preg_replace('/<\?php\s*/', '', $content);
    $content = preg_replace('/require_once.+;\s*/', '', $content);
    $content = preg_replace('/header\(.+\);\s*/', '', $content);

    // Replace $pdo with $this->service->getRepo()->getPDO()
    $content = str_replace('$pdo', '$pdo', $content); // We will inject $pdo at top

    $methodBody = "\n    public function {$camelMethod}() {\n        \$pdo = \$this->service->getRepo()->getPDO();\n" . $content . "\n    }\n";
    $controllerCode .= $methodBody;

    // Create dispatcher
    $dispatcher = "<?php\nrequire_once dirname(__DIR__, 3) . '/bootstrap/app.php';\nrequire_once dirname(__DIR__, 2) . '/Middleware/auth-admin.php';\n\nuse App\Controllers\Admin\AdminStaffBranchController;\nuse App\Services\AdminStaffBranchService;\nuse App\Repositories\AdminStaffBranchRepository;\n\n\$controller = new AdminStaffBranchController(new AdminStaffBranchService(new AdminStaffBranchRepository()));\n\$controller->{$camelMethod}();\n";
    file_put_contents($filePath, $dispatcher);
}

$controllerCode .= "\n}\n";
$serviceCode .= "\n}\n";

if (!is_dir(__DIR__ . '/app/Controllers/Admin')) mkdir(__DIR__ . '/app/Controllers/Admin', 0777, true);
if (!is_dir(__DIR__ . '/app/Services')) mkdir(__DIR__ . '/app/Services', 0777, true);
if (!is_dir(__DIR__ . '/app/Repositories')) mkdir(__DIR__ . '/app/Repositories', 0777, true);

file_put_contents($controllerPath, $controllerCode);
file_put_contents($servicePath, $serviceCode);
// file_put_contents($repoPath, $repoCode); // We already wrote repo

echo json_encode(["status" => "success"]);
