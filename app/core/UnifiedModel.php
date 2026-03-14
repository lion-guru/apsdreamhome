<?php

namespace App\Core;

use App\Core\Database\Model;

/**
 * UnifiedModel compatibility layer
 * Serves as a base for models that were previously using a unified architecture
 */
abstract class UnifiedModel extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected array $casts = [];

    /**
     * Create a new model instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}
