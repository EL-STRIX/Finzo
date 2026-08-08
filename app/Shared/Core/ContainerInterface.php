<?php

namespace App\Shared\Core;

/**
 * Basic PSR-11 compliant Container Interface
 */
interface ContainerInterface
{
    /**
     * Finds an entry of the container by its identifier and returns it.
     */
    public function get(string $id);

    /**
     * Returns true if the container can return an entry for the given identifier.
     */
    public function has(string $id): bool;
}
