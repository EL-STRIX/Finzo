<?php
namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Application\Services\StaffService;
use App\Infrastructure\Repositories\StaffRepository;
use App\Shared\Core\Exceptions\ResourceNotFoundException;
use App\Shared\Core\Exceptions\AuthenticationException;
use App\Shared\Core\Exceptions\BusinessRuleException;
use App\Shared\Security\SecurityService;
use PHPUnit\Framework\MockObject\MockObject;

class StaffServiceTest extends TestCase {
    
    private StaffService $staffService;
    /** @var StaffRepository&MockObject */
    private $staffRepoMock;
    
    protected function setUp(): void {
        parent::setUp();
        
        $this->staffRepoMock = $this->createMock(StaffRepository::class);
        $securityMock = $this->createMock(\App\Domain\Contracts\Security\SecurityServiceInterface::class);
        $this->staffService = new StaffService($this->staffRepoMock, $securityMock);
    }

    public function test_changePassword_throws_exception_if_staff_not_found() {
        $this->staffRepoMock->expects($this->once())
            ->method('findStaffPasswordHash')
            ->with(999)
            ->willReturn(false);
            
        $this->expectException(ResourceNotFoundException::class);
        $this->expectExceptionMessage('Staff account not found');
        
        $this->staffService->changePassword(999, 'old', 'new');
    }

    public function test_changePassword_throws_exception_if_current_password_incorrect() {
        $hash = password_hash('correct_password', PASSWORD_BCRYPT);
        
        $this->staffRepoMock->expects($this->once())
            ->method('findStaffPasswordHash')
            ->with(1)
            ->willReturn($hash);
            
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Current password is incorrect');
        
        $this->staffService->changePassword(1, 'wrong_password', 'new');
    }

    public function test_processWithdrawal_throws_exception_if_insufficient_balance() {
        $account = [
            'id' => 10,
            'account_no' => 'FNZ123',
            'status' => 'active',
            'balance' => 500.00,
            'daily_limit' => 5000.00,
            'full_name' => 'John Doe'
        ];
        $this->staffRepoMock->expects($this->once())->method('beginTransaction');
        $this->staffRepoMock->expects($this->once())->method('rollBack');
        
        $this->staffRepoMock->expects($this->once())
            ->method('findAccountForUpdate')
            ->with('FNZ123')
            ->willReturn($account);
            
        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('Insufficient balance. Available: ₹500.00');
        
        $this->staffService->processWithdrawal(1, 'FNZ123', 600.00, 'Test');
    }
}
