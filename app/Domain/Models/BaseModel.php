<?php

namespace App\Domain\Models;

abstract class BaseModel implements \ArrayAccess, \JsonSerializable
{
    protected array $attributes = [];

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    public function __get($name)
    {
        return $this->attributes[$name] ?? null;
    }

    public function __isset($name)
    {
        return isset($this->attributes[$name]);
    }

    public function __unset($name)
    {
        unset($this->attributes[$name]);
    }

    // ArrayAccess implementation
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->attributes[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->attributes[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset)) {
            $this->attributes[] = $value;
        } else {
            $this->attributes[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->attributes[$offset]);
    }

    // JsonSerializable implementation
    public function jsonSerialize(): mixed
    {
        return $this->attributes;
    }

    public function toArray(): array
    {
        return $this->attributes;
    }
}
