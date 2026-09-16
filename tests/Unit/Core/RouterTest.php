<?php

namespace Tests\Unit\Core;

use Tests\TestCase;
use App\Shared\Core\Router;
use App\Shared\Core\Exceptions\TestResponseException;

class RouterTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        parent::setUp();
        $this->router = new Router();
    }

    public function testRouteRegistrationMethods(): void
    {
        $handler = function () {
            return ['status' => 'ok'];
        };

        $this->router->get('/test-get', $handler);
        $this->router->post('/test-post', $handler);
        $this->router->put('/test-put', $handler);
        $this->router->patch('/test-patch', $handler);
        $this->router->delete('/test-delete', $handler);

        $routes = $this->router->getRoutes();
        $this->assertCount(5, $routes);

        $methods = array_column($routes, 'method');
        $this->assertContains('GET', $methods);
        $this->assertContains('POST', $methods);
        $this->assertContains('PUT', $methods);
        $this->assertContains('PATCH', $methods);
        $this->assertContains('DELETE', $methods);
    }

    public function testParameterPatternConversion(): void
    {
        $this->router->get('/users/{id}/orders/{order_id}', function () {
            return [];
        });

        $routes = $this->router->getRoutes();
        $this->assertCount(1, $routes);
        $pattern = $routes[0]['pattern'];

        $this->assertMatchesRegularExpression($pattern, '/users/123/orders/abc-99');
        $this->assertDoesNotMatchRegularExpression($pattern, '/users/123/orders');
    }

    public function testDispatchExecutesClosureHandler(): void
    {
        $executed = false;
        $capturedParam = null;

        $this->router->get('/accounts/{id}', function ($id) use (&$executed, &$capturedParam) {
            $executed = true;
            $capturedParam = $id;
            return ['account_id' => $id];
        });

        try {
            $this->router->dispatch('GET', '/accounts/FNZ8899');
        } catch (TestResponseException $e) {
            $json = json_decode($e->getMessage(), true);
            $this->assertTrue($executed);
            $this->assertEquals('FNZ8899', $capturedParam);
            $this->assertEquals('FNZ8899', $json['account_id']);
        }
    }

    public function testDispatchWithBaseUrlPrefix(): void
    {
        $executed = false;
        $this->router->get('/dashboard', function () use (&$executed) {
            $executed = true;
            return ['success' => true];
        });

        try {
            $this->router->dispatch('GET', '/Finzo/public/dashboard');
        } catch (TestResponseException $e) {
            $this->assertTrue($executed);
        }
    }

    public function testDispatchUnmatchedRouteReturns404(): void
    {
        try {
            $this->router->dispatch('GET', '/non-existent-route-999');
            $this->fail('Expected TestResponseException with 404');
        } catch (TestResponseException $e) {
            $json = json_decode($e->getMessage(), true);
            $this->assertIsArray($json);
            $this->assertFalse($json['success']);
            $this->assertEquals(404, $json['status']);
        }
    }
}
