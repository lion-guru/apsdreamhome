<?php

// TODO: Add proper error handling with try-catch blocks

namespace App\Core;

use App\Core\Database\Model;

/**
 * Base Model Class
 * Provides core database functionality for all models
 */
abstract class BaseModel extends Model
{
    protected static $legacyMode = false;
    protected $attributes = [];

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

    public static function setLegacyMode(bool $enabled)
    {
        static::$legacyMode = $enabled;
    }

    public static function find($id)
    {
        return static::$legacyMode ? static::findLegacy($id) : static::find($id);
    }

    protected static function findLegacy($id)
    {
        return static::find($id); // Fallback to modern method
    }

    public static function all()
    {
        return static::$legacyMode ? static::allLegacy() : static::all();
    }

    protected static function allLegacy()
    {
        return static::all(); // Fallback to modern method
    }

    public function save()
    {
        return static::$legacyMode ? $this->saveLegacy() : $this->save();
    }

    protected function saveLegacy()
    {
        return $this->save(); // Fallback to modern method
    }

    public function delete()
    {
        return static::$legacyMode ? $this->deleteLegacy() : $this->delete();
    }

    protected function deleteLegacy()
    {
        return $this->delete(); // Fallback to modern method
    }

    public static function isLegacyMode()
    {
        return static::$legacyMode;
    }
}
