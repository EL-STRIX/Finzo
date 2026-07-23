<?php

namespace App\Shared\Core\Events;

class EventDispatcher
{
    private static ?EventDispatcher $instance = null;
    private array $listeners = [];

    private function __construct() {}

    public static function getInstance(): EventDispatcher
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function listen(string $eventName, callable|array|string $listener): void
    {
        $this->listeners[$eventName][] = $listener;
    }

    public function dispatch(object $event): void
    {
        $eventName = get_class($event);
        if (isset($this->listeners[$eventName])) {
            foreach ($this->listeners[$eventName] as $listener) {
                if (is_callable($listener)) {
                    call_user_func($listener, $event);
                } elseif (is_array($listener) && count($listener) === 2) {
                    $class = $listener[0];
                    $method = $listener[1];
                    $instance = is_object($class) ? $class : new $class();
                    $instance->$method($event);
                } elseif (is_string($listener) && class_exists($listener)) {
                    $instance = new $listener();
                    $instance->handle($event);
                }
            }
        }
    }
}
