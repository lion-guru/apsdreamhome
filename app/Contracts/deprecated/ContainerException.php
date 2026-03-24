<?php

namespace App\Contracts;

/**
 * Container Exception
 * Base exception for container-related errors
 */
class ContainerException extends \Exception
{
    public function __construct(string $message = "", int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message ?: "Container error occurred", $code, $previous);
    }
}
