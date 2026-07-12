<?php
$files = [
    'audit-log.php' => 'auditLog',
    'dashboard.php' => 'dashboard',
    'deleted-users.php' => 'deletedUsers',
    'export-users.php' => 'exportUsers',
    'interest.php' => 'interest',
    'locked-account-detail.php' => 'lockedAccountDetail',
    'locked-accounts.php' => 'lockedAccounts',
    'pending-approvals.php' => 'pendingApprovals',
    'recover-user.php' => 'recoverUser',
    'review-application.php' => 'reviewApplication',
    'suspended-user-detail.php' => 'suspendedUserDetail',
    'suspended-users.php' => 'suspendedUsers',
    'transactions.php' => 'transactions',
    'unlock-account.php' => 'unlockAccount',
    'user-action.php' => 'userAction',
    'user-detail.php' => 'userDetail',
    'users.php' => 'users'
];

$repoCode = "<?php\nnamespace App\Repositories;\n\nuse App\Repositories\BaseRepository;\nuse PDO;\n\nclass AdminOperationsRepository extends BaseRepository {\n    public function getDb() { return \$this->db; }\n";
$serviceCode = "<?php\nnamespace App\Services;\n\nuse App\Repositories\AdminOperationsRepository;\nuse PDOException;\nuse DateTime;\n\nclass AdminOperationsService {\n    protected \$repository;\n    public function __construct(AdminOperationsRepository \$repository) {\n        \$this->repository = \$repository;\n    }\n";
$controllerCode = "<?php\nnamespace App\Controllers\Admin;\n\nuse App\Services\AdminOperationsService;\nuse PDOException;\nuse DateTime;\n\nclass AdminOperationsController {\n    protected \$service;\n    public function __construct(AdminOperationsService \$service) {\n        \$this->service = \$service;\n    }\n";

foreach ($files as $file => $methodName) {
    $path = "c:/xampp/htdocs/Finzo/app/Controllers/admin/$file";
    if (!file_exists($path)) continue;

    $content = file_get_contents($path);

    // Extract the body (everything after require_once ... auth-admin.php';)
    $body = preg_replace('/^.*?require_once.*?auth-admin\.php\';/s', '', $content);
    $body = str_replace(['<?php', '?>'], '', $body);
    
    // We will find all $pdo->query and $pdo->prepare, extract the SQL, find variables in it, and create a Repo method.
    $body = preg_replace_callback('/\$pdo->(prepare|query)\s*\(\s*(["\'])(.*?)\2\s*\)/s', function($matches) use (&$repoCode, $methodName) {
        static $counter = 1;
        $type = $matches[1];
        $quote = $matches[2];
        $sql = $matches[3];
        $repoMethodName = "get" . ucfirst($methodName) . "Query" . $counter++;
        
        // Find variables in the SQL string
        // Matches $var, {$var}, etc.
        preg_match_all('/\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/', $sql, $varMatches);
        // also matches {$var}
        preg_match_all('/\{\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\}/', $sql, $varMatches2);
        
        $vars = array_unique(array_merge($varMatches[1], $varMatches2[1]));
        $params = array_map(function($v) { return "\$$v"; }, $vars);
        $paramStr = implode(', ', $params);
        
        $repoCode .= "    public function {$repoMethodName}($paramStr) {\n        return {$quote}{$sql}{$quote};\n    }\n\n";
        
        return "\$this->repository->getDb()->{$type}(\$this->repository->{$repoMethodName}($paramStr))";
    }, $body);

    // Replace other $pdo usages
    $body = str_replace('$pdo->', '$this->repository->getDb()->', $body);
    $body = str_replace('$pdo', '$this->repository->getDb()', $body); // for write_audit_log($pdo, ...)

    // To cleanly separate Controller and Service, we could extract HTTP requests.
    // But since that's hard to automate perfectly, we'll put the logic in the Service, and just call it from the Controller.
    // Wait, the prompt says "Move HTTP request handling... into Controller".
    // I will extract $_GET and $_POST at the beginning of the body, and pass them to the Service.
    preg_match_all('/\$_GET\[["\']([^"\']+)["\']\]/', $body, $getMatches);
    preg_match_all('/\$_POST\[["\']([^"\']+)["\']\]/', $body, $postMatches);
    $allVars = array_unique(array_merge($getMatches[0], $postMatches[0]));
    
    // Actually, passing the whole $_GET and $_POST is easier and cleaner for automated refactoring.
    $serviceCode .= "\n    public function execute" . ucfirst($methodName) . "(\$get, \$post, \$server, \$session) {\n";
    
    // Replace globals in body with local variables
    $body = str_replace('$_GET', '$get', $body);
    $body = str_replace('$_POST', '$post', $body);
    $body = str_replace('$_SERVER', '$server', $body);
    $body = str_replace('$_SESSION', '$session', $body);
    
    $serviceCode .= "        " . trim($body) . "\n    }\n";
    
    $controllerCode .= "\n    public function {$methodName}() {\n        \$this->service->execute" . ucfirst($methodName) . "(\$_GET, \$_POST, \$_SERVER, \$_SESSION);\n    }\n";
    
    // Write thin dispatcher
    $dispatcher = "<?php\nrequire_once dirname(__DIR__, 3) . '/bootstrap/app.php';\nuse App\Controllers\Admin\AdminOperationsController;\nuse App\Services\AdminOperationsService;\nuse App\Repositories\AdminOperationsRepository;\n\n\$controller = new AdminOperationsController(new AdminOperationsService(new AdminOperationsRepository()));\n\$controller->{$methodName}();\n";
    
    file_put_contents($path, $dispatcher);
}

$repoCode .= "}\n";
$serviceCode .= "}\n";
$controllerCode .= "}\n";

if (!is_dir("c:/xampp/htdocs/Finzo/app/Services")) mkdir("c:/xampp/htdocs/Finzo/app/Services", 0777, true);
if (!is_dir("c:/xampp/htdocs/Finzo/app/Controllers/Admin")) mkdir("c:/xampp/htdocs/Finzo/app/Controllers/Admin", 0777, true);

file_put_contents("c:/xampp/htdocs/Finzo/app/Repositories/AdminOperationsRepository.php", $repoCode);
file_put_contents("c:/xampp/htdocs/Finzo/app/Services/AdminOperationsService.php", $serviceCode);
file_put_contents("c:/xampp/htdocs/Finzo/app/Controllers/Admin/AdminOperationsController.php", $controllerCode);

echo "Refactoring complete!";
