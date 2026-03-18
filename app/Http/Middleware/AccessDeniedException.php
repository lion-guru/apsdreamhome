<?php

namespace App\Http\Middleware;

use Exception;

/**
 * Access Denied Exception
 * Thrown when access is denied to a resource
 */
class AccessDeniedException extends Exception
{
    protected $message = 'Access Denied';
    protected $code = 403;
}
