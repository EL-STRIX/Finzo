<?php
namespace Tests\Unit\Core;

use Tests\TestCase;
use App\Shared\Core\Validation\Validator;
use App\Shared\Core\Exceptions\ValidationException;

class ValidatorTest extends TestCase {
    
    public function testSanitizeRemovesTagsAndWhitespace() {
        $data = [
            'name' => '  <b>John</b>  ',
            'script' => '<script>alert(1)</script>',
            'number' => 123
        ];
        
        $cleaned = Validator::sanitize($data);
        
        $this->assertEquals('John', $cleaned['name']);
        $this->assertEquals('alert(1)', $cleaned['script']);
        $this->assertEquals(123, $cleaned['number']);
    }

    public function testValidatePassesValidData() {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => '25'
        ];
        
        $rules = [
            'name' => 'required|min:3|max:50',
            'email' => 'required|email',
            'age' => 'required|numeric'
        ];
        
        $validated = Validator::validate($data, $rules);
        
        $this->assertEquals('John Doe', $validated['name']);
        $this->assertEquals('john@example.com', $validated['email']);
        $this->assertEquals('25', $validated['age']);
    }

    public function testValidateThrowsExceptionOnMissingRequiredField() {
        $data = [
            'email' => 'john@example.com'
        ];
        
        $rules = [
            'name' => 'required',
            'email' => 'required|email'
        ];
        
        $this->expectException(ValidationException::class);
        
        Validator::validate($data, $rules);
    }
    
    public function testValidateEmailRule() {
        $data = [
            'email' => 'invalid-email'
        ];
        
        $rules = [
            'email' => 'required|email'
        ];
        
        try {
            Validator::validate($data, $rules);
            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('email', $e->getPayload()['errors']);
            $this->assertEquals('Please enter a valid email address.', $e->getPayload()['errors']['email']);
        }
    }

    public function testValidateMinAndMaxRules() {
        $data = [
            'short' => 'a',
            'long' => 'abcdefghijklm'
        ];
        
        $rules = [
            'short' => 'min:3',
            'long' => 'max:10'
        ];
        
        try {
            Validator::validate($data, $rules);
            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('short', $e->getPayload()['errors']);
            $this->assertArrayHasKey('long', $e->getPayload()['errors']);
            $this->assertEquals('Short must be at least 3 characters.', $e->getPayload()['errors']['short']);
            $this->assertEquals('Long must not exceed 10 characters.', $e->getPayload()['errors']['long']);
        }
    }
    
    public function testValidateNumericRule() {
        $data = [
            'amount' => 'abc'
        ];
        
        $rules = [
            'amount' => 'numeric'
        ];
        
        try {
            Validator::validate($data, $rules);
            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('amount', $e->getPayload()['errors']);
            $this->assertEquals('Amount must be a number.', $e->getPayload()['errors']['amount']);
        }
    }
}
