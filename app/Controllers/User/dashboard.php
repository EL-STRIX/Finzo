<?php
require_once dirname(__DIR__, 2) . '/../bootstrap/app.php';
require_once dirname(__DIR__, 2) . '/Middleware/auth-user.php';
use App\Controllers\User\UserController;
use App\Services\UserService;
use App\Repositories\UserRepository;

$controller = new UserController(new UserService(new UserRepository()));
$controller->dashboard();
