<?php

namespace App\Models;

use App\Core\Model as CoreModel;

abstract class Model extends CoreModel
{
    protected static $table = '';

    /**
     * Override fill to maintain backward compatibility allowing 'id'
     */
    public function fill(array $attributes): self
    {
        // Use parent logic but ensure 'id' is allowed if passed
        if (isset($attributes['id'])) {
            $this->attributes['id'] = $attributes['id'];
        }

        return parent::fill($attributes);
    }
}
