<?php

namespace Tests\Unit\Core;

use Tests\TestCase;
use App\Shared\Core\Container;
use App\Infrastructure\Repositories\UserSecurityRepository;
use App\Application\Services\UserSecurityService;
use Exception;
use stdClass;

class ContainerTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = Container::getInstance();
    }

    public function testSingletonReturnsSameInstance(): void
    {
        $this->container->singleton('dummy_singleton_key', function () {
            $obj = new stdClass();
            $obj->created_at = microtime(true);
            return $obj;
        });

        $obj1 = $this->container->get('dummy_singleton_key');
        $obj2 = $this->container->get('dummy_singleton_key');

        $this->assertSame($obj1, $obj2);
    }

    public function testBindInterfaceToConcreteClosure(): void
    {
        $this->container->bind('custom_test_binding', function () {
            $obj = new stdClass();
            $obj->title = 'Finzo Container';
            return $obj;
        });

        $this->assertTrue($this->container->has('custom_test_binding'));
        $instance = $this->container->get('custom_test_binding');
        $this->assertInstanceOf(stdClass::class, $instance);
        $this->assertEquals('Finzo Container', $instance->title);
    }

    public function testAutowiringNestedDependencies(): void
    {
        $instance = $this->container->get(UserSecurityService::class);
        $this->assertInstanceOf(UserSecurityService::class, $instance);
    }

    public function testNonExistentClassThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->container->get('NonExistentClassString12345');
    }
}
