<?php

require_once dirname(__DIR__, 3) . '/../bootstrap/app.php';
require_once dirname(__DIR__, 3) . '/Presentation/Middleware/auth-user.php';
use App\Presentation\Controllers\User\UserController;
use App\Application\Services\UserService;
use App\Infrastructure\Repositories\UserRepository;

$controller = new UserController(new UserService(new UserRepository()));
$controller->profile();
