<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Application\Services\CardService;
use App\Infrastructure\Repositories\CardRepository;

class CardServiceTest extends TestCase
{
    private $cardService;
    private $cardRepo;

    protected function setUp(): void
    {
        parent::setUp();
        
        if (!defined('FINZO_ROOT')) {
            define('FINZO_ROOT', dirname(dirname(dirname(__DIR__))));
        }
        require_once FINZO_ROOT . '/config/functions.php';
        
        if (!defined('APP_KEY')) {
            define('APP_KEY', base64_encode(str_repeat('a', 32)));
        }
        
        $this->cardRepo = new CardRepository($this->db);
        
        $userRepoMock = $this->createMock(\App\Domain\Contracts\Repositories\UserRepositoryInterface::class);
        $adminOpsRepoMock = $this->createMock(\App\Domain\Contracts\Repositories\AdminOperationsRepositoryInterface::class);
        $securityMock = $this->createMock(\App\Domain\Contracts\Security\SecurityServiceInterface::class);
        
        $this->cardService = new CardService(
            $this->cardRepo,
            $userRepoMock,
            $adminOpsRepoMock,
            $securityMock
        );
    }

    public function testGetCardProducts()
    {
        $products = $this->cardService->getCardProducts('all');
        $this->assertIsArray($products);
    }

    public function testGetCardRequests()
    {
        $requests = $this->cardService->getCardRequests();
        $this->assertIsArray($requests);
    }
    
    public function testVerifyPinReturnsFalseForInvalid()
    {
        $result = $this->cardService->verifyPin(999, 999, '0000');
        $this->assertFalse($result);
    }
    
    public function testGetCardStatement()
    {
        $this->expectException(\App\Shared\Core\Exceptions\ResourceNotFoundException::class);
        $statement = $this->cardService->getCardStatement(999, 999);
    }
}
