<?php

namespace App\Shared\Core;

class Container implements ContainerInterface
{
    private static ?Container $instance = null;
    private array $bindings = [];
    private array $instances = [];

    private function __construct() {}

    public static function getInstance(): Container
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function bind(string $abstract, $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
    }

    public function singleton(string $abstract, $concrete): void
    {
        $this->bind($abstract, $concrete);
        $this->instances[$abstract] = null; // Mark as singleton
    }

    public function has(string $id): bool
    {
        return isset($this->bindings[$id]) || isset($this->instances[$id]);
    }

    public function get(string $id)
    {
        if (array_key_exists($id, $this->instances) && $this->instances[$id] !== null) {
            return $this->instances[$id];
        }

        if (!isset($this->bindings[$id])) {
            // Attempt auto-wiring if class exists
            if (class_exists($id)) {
                return $this->build($id);
            }
            throw new \Exception("Target [$id] is not instantiable.");
        }

        $concrete = $this->bindings[$id];

        if ($concrete instanceof \Closure) {
            $object = $concrete($this);
        } else {
            $object = $this->build($concrete);
        }

        if (array_key_exists($id, $this->instances)) {
            $this->instances[$id] = $object;
        }

        return $object;
    }

    private function build(string $concrete)
    {
        $reflection = new \ReflectionClass($concrete);

        if (!$reflection->isInstantiable()) {
            throw new \Exception("Target [$concrete] is not instantiable.");
        }

        $constructor = $reflection->getConstructor();

        if (is_null($constructor)) {
            return new $concrete();
        }

        $dependencies = $constructor->getParameters();
        $instances = [];

        foreach ($dependencies as $dependency) {
            $type = $dependency->getType();
            if ($type && !$type->isBuiltin()) {
                $dependencyName = $type->getName();
                $instances[] = $this->get($dependencyName);
            } else {
                if ($dependency->isDefaultValueAvailable()) {
                    $instances[] = $dependency->getDefaultValue();
                } else {
                    throw new \Exception("Cannot resolve non-class dependency {$dependency->name} in class {$concrete}");
                }
            }
        }

        return $reflection->newInstanceArgs($instances);
    }
}
