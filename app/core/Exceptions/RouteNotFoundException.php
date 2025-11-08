<?php

namespace App\Core\Exceptions;

use Exception;

class RouteNotFoundException extends Exception
{
    /**
     * Create a new exception instance.
     *
     * @param  string  $message
     * @param  int  $code
     * @param  \Throwable|null  $previous
     * @return void
     */
    public function __construct($message = "Route not found", $code = 404, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
