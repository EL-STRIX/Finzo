<?php
namespace Tests\Unit\Validators;

use Tests\TestCase;
use App\Presentation\Validators\AuthValidator;
use App\Shared\Core\Exceptions\ApiException;

class AuthValidatorTest extends TestCase {
    
    protected function setUp(): void {
        parent::setUp();
        // Set a fake CSRF token in session
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $_SESSION['csrf_token'] = 'valid-token';
        $_SESSION['csrf_token_time'] = time();
    }
    
    public function testValidateLoginPassesWithValidData() {
        // This should not throw an exception
        $this->expectNotToPerformAssertions();
        AuthValidator::validateLogin('user@example.com', 'password', 'valid-token');
    }
    
    public function testValidateLoginThrowsExceptionOnMissingCsrf() {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Security token missing');
        AuthValidator::validateLogin('user@example.com', 'password', '');
    }
    
    public function testValidateLoginThrowsExceptionOnInvalidCsrf() {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Invalid or expired security session');
        AuthValidator::validateLogin('user@example.com', 'password', 'invalid-token');
    }
    
    public function testValidateLoginThrowsExceptionOnMissingIdentity() {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Identity and password are required');
        AuthValidator::validateLogin('', 'password', 'valid-token');
    }

    public function testValidateLoginThrowsExceptionOnMissingPassword() {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Identity and password are required');
        AuthValidator::validateLogin('user@example.com', '', 'valid-token');
    }
}
