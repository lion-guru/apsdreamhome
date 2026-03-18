<?php

namespace App\Models;

use Exception;

/**
 * Model Exception Class
 * Base exception for all model operations
 */
class ModelException extends Exception
{
    protected $message = 'Model operation failed';
    protected $code = 500;
}
