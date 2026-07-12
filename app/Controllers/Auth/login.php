<?php
require_once dirname(__DIR__, 2) . '/../bootstrap/app.php';

use App\Controllers\Auth\AuthController;
use App\Services\AuthService;
use App\Repositories\AuthRepository;

$controller = new AuthController(new AuthService(new AuthRepository()));
$controller->login();
