<?php

namespace App\Contracts;

/**
 * PSR-11 Compatible Container Interface
 * Defines standard methods for dependency injection containers
 */
interface ContainerInterface
{
    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     * @return mixed Entry.
     * @throws ServiceNotFoundException No entry was found for this identifier.
     * @throws ContainerException Error while retrieving the entry.
     */
    public function get(string $id);

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * @param string $id Identifier of the entry to look for.
     * @return bool
     */
    public function has(string $id): bool;

    /**
     * Register a service in the container.
     *
     * @param string $id Service identifier
     * @param mixed $definition Service definition (class name, closure, or instance)
     * @param bool $shared Whether the service should be shared (singleton)
     * @return self
     */
    public function register(string $id, $definition, bool $shared = false): self;

    /**
     * Remove a service from the container.
     *
     * @param string $id Service identifier
     * @return self
     */
    public function remove(string $id): self;

    /**
     * Get all registered service identifiers.
     *
     * @return array
     */
    public function getRegisteredServices(): array;

    /**
     * Clear all services from the container.
     *
     * @return self
     */
    public function clear(): self;
}

/**
 * Exception for when a service is not found in the container
 */
class ServiceNotFoundException extends \Exception
{
    public function __construct(string $id, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct("Service '{$id}' not found in container", $code, $previous);
    }
}

/**
 * Exception for general container-related errors
 */
class ContainerException extends \Exception
{
    public function __construct(string $message = "Container error", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
