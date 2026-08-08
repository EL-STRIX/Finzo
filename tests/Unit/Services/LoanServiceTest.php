<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Application\Services\LoanService;
use App\Infrastructure\Repositories\LoanRepository;

class LoanServiceTest extends TestCase
{
    private $loanService;
    private $loanRepo;

    protected function setUp(): void
    {
        parent::setUp();
        
        if (!defined('FINZO_ROOT')) {
            define('FINZO_ROOT', dirname(dirname(dirname(__DIR__))));
        }
        require_once FINZO_ROOT . '/config/functions.php';
        
        $this->loanRepo = new LoanRepository($this->db);
        
        $userRepoMock = $this->createMock(\App\Domain\Contracts\Repositories\UserRepositoryInterface::class);
        $adminOpsRepoMock = $this->createMock(\App\Domain\Contracts\Repositories\AdminOperationsRepositoryInterface::class);
        $securityMock = $this->createMock(\App\Domain\Contracts\Security\SecurityServiceInterface::class);
        
        $this->loanService = new LoanService(
            $this->loanRepo,
            $userRepoMock,
            $adminOpsRepoMock,
            $securityMock
        );
    }

    public function testGetLoanProducts()
    {
        $products = $this->loanService->getLoanProducts('all');
        $this->assertIsArray($products);
    }

    public function testGetLoanApplications()
    {
        $applications = $this->loanService->getLoanApplications();
        $this->assertIsArray($applications);
    }
    

}
