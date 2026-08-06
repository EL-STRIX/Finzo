<?php
namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Application\Services\AuthService;
use App\Infrastructure\Repositories\AuthRepository;
use App\Shared\Core\Exceptions\ApiException;
use PHPUnit\Framework\MockObject\MockObject;

class AuthServiceTest extends TestCase {
    
    private AuthService $authService;
    /** @var AuthRepository&MockObject */
    private $authRepoMock;
    
    protected function setUp(): void {
        parent::setUp();
        ini_set('display_errors', '1');
        error_reporting(E_ALL);
        
        $this->authRepoMock = $this->createMock(AuthRepository::class);
        $this->authService = new AuthService($this->authRepoMock);
    }

    public function test_loginUser_with_valid_credentials() {
        $user = [
            'id' => 1,
            'customer_id' => 'CUST123',
            'full_name' => 'John Doe',
            'email' => 'valid@test.com',
            'phone' => '1234567890',
            'password_hash' => password_hash('CorrectPassword123', PASSWORD_BCRYPT),
            'status' => 'active',
            'locked_until' => null,
            'failed_attempts' => 0
        ];
        
        $this->authRepoMock->expects($this->once())
            ->method('findUserByIdentity')
            ->with('valid@test.com')
            ->willReturn($user);
            
        // The service fetches setting for max_attempts, but it uses the static `get_setting` which requires a real DB connection or we need to bypass it.
        // Wait, the service gets a PDO instance: $pdo = $this->repository->getPDO();
        $dbMock = $this->createMock(\PDO::class);
        $stmtMock = $this->createMock(\PDOStatement::class);
        $stmtMock->method('fetchColumn')->willReturn(5);
        $dbMock->method('prepare')->willReturn($stmtMock);
        $this->authRepoMock->method('getPDO')->willReturn($dbMock);

        $this->authRepoMock->expects($this->once())
            ->method('updateSuccessfulLogin');
            
        $this->authRepoMock->expects($this->once())
            ->method('fetchActiveAccount')
            ->with('CUST123')
            ->willReturn(['account_no' => 'ACC123', 'balance' => 5000.00]);
        
        $result = $this->authService->loginUser('valid@test.com', 'CorrectPassword123');
        
        $this->assertEquals('CUST123', $result['user']['customer_id']);
        $this->assertEquals('ACC123', $result['account']['account_no']);
    }

    public function test_loginUser_with_invalid_credentials_throws_exception() {
        $user = [
            'id' => 1,
            'email' => 'valid@test.com',
            'password_hash' => password_hash('CorrectPassword123', PASSWORD_BCRYPT),
            'status' => 'active',
            'locked_until' => null,
            'failed_attempts' => 0
        ];
        
        $this->authRepoMock->expects($this->once())
            ->method('findUserByIdentity')
            ->willReturn($user);
            
        $dbMock = $this->createMock(\PDO::class);
        // We'd have to mock the PDO statement for get_setting, or just let it fail if it hits DB.
        // For a unit test, we should mock getDb to return a mock PDO that returns a mock PDOStatement for the setting query.
        $stmtMock = $this->createMock(\PDOStatement::class);
        $stmtMock->method('fetchColumn')->willReturn(5); // 5 max attempts
        $dbMock->method('prepare')->willReturn($stmtMock);
        
        $this->authRepoMock->method('getPDO')->willReturn($dbMock);

        $this->authRepoMock->expects($this->once())
            ->method('updateFailedAttempts')
            ->with($user['id'], 1);
            
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Invalid email or password');
        
        $this->authService->loginUser('valid@test.com', 'WrongPassword123');
    }

    public function test_loginUser_with_locked_account() {
        $user = [
            'id' => 1,
            'email' => 'locked@test.com',
            'password_hash' => password_hash('CorrectPassword123', PASSWORD_BCRYPT),
            'status' => 'active',
            'locked_until' => date('Y-m-d H:i:s', time() + 3600), // Locked for 1 hour
            'failed_attempts' => 5
        ];
        
        $this->authRepoMock->expects($this->once())
            ->method('findUserByIdentity')
            ->willReturn($user);
            
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Account locked');
        
        $this->authService->loginUser('locked@test.com', 'AnyPassword');
    }
}
