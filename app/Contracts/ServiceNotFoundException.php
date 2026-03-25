<?php

namespace App\Contracts;

/**
 * Service Not Found Exception
 * Thrown when a requested service is not found in the container
 */
class ServiceNotFoundException extends \Exception
{
    private string $serviceId;

    public function __construct(string $serviceId, string $message = "", int $code = 0, \Throwable $previous = null)
    {
        $this->serviceId = $serviceId;
        parent::__construct($message ?: "Service '{$serviceId}' not found", $code, $previous);
    }

    public function getServiceId(): string
    {
        return $this->serviceId;
    }
}
