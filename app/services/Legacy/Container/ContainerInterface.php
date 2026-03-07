<?php

namespace App\Services\Legacy;
/**
 * PSR-11 Compatible Container Interface
 * Defines standard methods for dependency injection containers
 */
interface ContainerInterface {
    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     * @return mixed Entry.
     * @throws ServiceNotFoundException No entry was found for this identifier.
     * @throws ContainerException Error while retrieving the entry.
     */
    public function get($id);

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * @param string $id Identifier of the entry to look for.
     * @return bool
     */
    public function has($id);
}

/**
 * Exception for when a service is not found in the container
 */
class ServiceNotFoundException extends \Exception {}

/**
 * Exception for general container-related errors
 */
class ContainerException extends \Exception {}
