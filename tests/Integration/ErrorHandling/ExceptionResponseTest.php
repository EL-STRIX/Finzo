<?php
namespace Tests\Integration\ErrorHandling;

use Tests\TestCase;
use App\Shared\Core\Exceptions\ExceptionHandler;
use App\Shared\Core\Exceptions\ValidationException;
use App\Shared\Core\Exceptions\AuthenticationException;
use App\Shared\Core\Exceptions\AuthorizationException;
use App\Shared\Core\Exceptions\ResourceNotFoundException;
use App\Shared\Core\Exceptions\BusinessRuleException;
use App\Shared\Core\Exceptions\ApiException;

class ExceptionResponseTest extends TestCase {

    private function captureExceptionResponse(\Throwable $e): array {
        ob_start();
        ExceptionHandler::handle($e);
        $output = ob_get_clean();
        return json_decode($output, true) ?? [];
    }

    public function testValidationExceptionReturns422() {
        $e = new ValidationException('Validation failed', ['errors' => ['email' => 'Invalid format']]);
        $response = $this->captureExceptionResponse($e);
        
        $this->assertFalse($response['success']);
        $this->assertEquals(422, $response['status']);
        $this->assertEquals('Validation failed', $response['message']);
        $this->assertArrayHasKey('errors', $response);
        $this->assertEquals('Invalid format', $response['errors']['email']);
    }

    public function testAuthenticationExceptionReturns401() {
        $e = new AuthenticationException('Unauthenticated');
        $response = $this->captureExceptionResponse($e);
        
        $this->assertFalse($response['success']);
        $this->assertEquals(401, $response['status']);
        $this->assertEquals('Unauthenticated', $response['message']);
    }

    public function testAuthorizationExceptionReturns403() {
        $e = new AuthorizationException('Access denied');
        $response = $this->captureExceptionResponse($e);
        
        $this->assertFalse($response['success']);
        $this->assertEquals(403, $response['status']);
        $this->assertEquals('Access denied', $response['message']);
    }

    public function testResourceNotFoundExceptionReturns404() {
        $e = new ResourceNotFoundException('User not found');
        $response = $this->captureExceptionResponse($e);
        
        $this->assertFalse($response['success']);
        $this->assertEquals(404, $response['status']);
        $this->assertEquals('User not found', $response['message']);
    }

    public function testBusinessRuleExceptionReturns400() {
        $e = new BusinessRuleException('Cannot transfer to self');
        $response = $this->captureExceptionResponse($e);
        
        $this->assertFalse($response['success']);
        $this->assertEquals(400, $response['status']);
        $this->assertEquals('Cannot transfer to self', $response['message']);
    }

    public function testGenericApiExceptionReturnsCustomStatus() {
        $e = new ApiException('Custom API Error', 418, ['errors' => ['teapot' => true]]);
        $response = $this->captureExceptionResponse($e);
        
        $this->assertFalse($response['success']);
        $this->assertEquals(418, $response['status']);
        $this->assertEquals('Custom API Error', $response['message']);
        
        $this->assertArrayHasKey('errors', $response);
        $this->assertTrue(isset($response['errors']['teapot']) && $response['errors']['teapot'], "Response errors did not contain teapot. Actual: " . json_encode($response));
    }

    public function testUnhandledExceptionReturns500WithoutStackTrace() {
        $e = new \RuntimeException('Database connection lost on line 52!');
        $response = $this->captureExceptionResponse($e);
        
        $this->assertFalse($response['success']);
        $this->assertEquals(500, $response['status']);
        $this->assertEquals('Internal Server Error', $response['message']);
        
        // Ensure sensitive stack trace data is NOT leaked in the response JSON
        $jsonString = json_encode($response);
        $this->assertStringNotContainsString('RuntimeException', $jsonString);
        $this->assertStringNotContainsString('Database connection lost', $jsonString);
    }
}
