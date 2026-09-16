<?php

namespace Tests\Unit\Core;

use Tests\TestCase;
use App\Shared\Core\Events\EventDispatcher;
use stdClass;

class EventDispatcherTest extends TestCase
{
    private EventDispatcher $dispatcher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dispatcher = EventDispatcher::getInstance();
    }

    public function testDispatchCallsClosureListener(): void
    {
        $received = null;
        $event = new stdClass();
        $event->name = 'user.registered';

        $this->dispatcher->listen(stdClass::class, function (stdClass $evt) use (&$received) {
            $received = $evt->name;
        });

        $this->dispatcher->dispatch($event);

        $this->assertEquals('user.registered', $received);
    }

    public function testDispatchCallsClassMethodListener(): void
    {
        $listener = new class {
            public string $handled = '';

            public function handleEvent(stdClass $evt): void
            {
                $this->handled = $evt->name;
            }
        };

        $event = new stdClass();
        $event->name = 'funds.transferred';

        $this->dispatcher->listen(stdClass::class, [$listener, 'handleEvent']);
        $this->dispatcher->dispatch($event);

        $this->assertEquals('funds.transferred', $listener->handled);
    }
}
