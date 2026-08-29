<?php
namespace Tests\Unit\Core;

use Tests\TestCase;
use App\Shared\Core\Http\ResponseBuilder;

class ResponseBuilderTest extends TestCase {
    
    public function testFormatSuccess() {
        $data = ['id' => 1, 'name' => 'test'];
        $response = ResponseBuilder::format($data, 200);
        
        $this->assertTrue($response['success']);
        $this->assertEquals(200, $response['status']);
        $this->assertEquals('Request successful.', $response['message']);
        // Flat payloads are merged to the response root
        $this->assertEquals(1, $response['id']);
        $this->assertEquals('test', $response['name']);
        $this->assertArrayHasKey('timestamp', $response);
        $this->assertArrayHasKey('request_id', $response);
    }

    public function testFormatSuccessWithCustomMessage() {
        $data = ['message' => 'Custom success', 'id' => 1];
        $response = ResponseBuilder::format($data, 201);
        
        $this->assertTrue($response['success']);
        $this->assertEquals(201, $response['status']);
        $this->assertEquals('Custom success', $response['message']);
        $this->assertEquals(1, $response['id']);
    }

    public function testFormatError() {
        $data = ['message' => 'Validation failed', 'errors' => ['field' => 'Required']];
        $response = ResponseBuilder::format($data, 422);
        
        $this->assertFalse($response['success']);
        $this->assertEquals(422, $response['status']);
        $this->assertEquals('Validation failed', $response['message']);
        $this->assertEquals(['field' => 'Required'], $response['errors']);
    }
    
    public function testFormatErrorString() {
        $response = ResponseBuilder::format('Not found', 404);
        
        $this->assertFalse($response['success']);
        $this->assertEquals(404, $response['status']);
        $this->assertEquals('Not found', $response['message']);
        $this->assertEquals((object)[], $response['errors']);
    }

    public function testScrubSensitiveData() {
        $data = [
            'id' => 1,
            'password' => 'secret',
            'token' => 'abc',
            'nested' => [
                'password_hash' => 'hash'
            ]
        ];
        
        $response = ResponseBuilder::format($data, 200);
        
        $this->assertTrue($response['success']);
        $this->assertEquals(1, $response['id']);
        $this->assertEquals([], $response['nested']);
    }

    public function testFormatSuccessWithNestedDataKey() {
        $response = ResponseBuilder::format(['data' => ['id' => 1]], 200);

        $this->assertTrue($response['success']);
        $this->assertEquals(['id' => 1], $response['data']);
    }
}
