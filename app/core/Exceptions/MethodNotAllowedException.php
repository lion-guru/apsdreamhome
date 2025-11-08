<?php

namespace App\Core\Exceptions;

use Exception;

class MethodNotAllowedException extends Exception
{
    /**
     * The allowed HTTP methods.
     *
     * @var array
     */
    protected $allowedMethods = [];

    /**
     * Create a new exception instance.
     *
     * @param  array  $allowedMethods
     * @param  string  $message
     * @param  int  $code
     * @param  \Throwable|null  $previous
     * @return void
     */
    public function __construct(
        array $allowedMethods = [],
        $message = "Method not allowed",
        $code = 405,
        $previous = null
    ) {
        $this->allowedMethods = $allowedMethods;
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the allowed HTTP methods.
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return $this->allowedMethods;
    }
}
