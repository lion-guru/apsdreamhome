<?php

namespace App\Core\Database;

use App\Core\App;
use App\Core\Contracts\Arrayable;
use App\Core\Database\Relations\HasRelationships;
use App\Core\Support\Str;
use App\Core\Support\Collection;
use PDO;
use RuntimeException;

if (!function_exists('class_basename')) {
    function class_basename($class) {
        $class = is_object($class) ? get_class($class) : $class;
        return basename(str_replace('\\', '/', $class));
    }
}

abstract class Model implements \ArrayAccess, \JsonSerializable
{
    // Continue with the rest of the Model class...
}
