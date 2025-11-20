<?php

namespace App\Core;

/**
 * Unified Model Base Class
 * Bridges legacy manager patterns with modern ORM features
 */
abstract class UnifiedModel extends Model
{
    protected static $legacyMode = false;
    
    public static function setLegacyMode(bool $enabled)
    {
        static::$legacyMode = $enabled;
    }
    
    public static function findUnified($id)
    {
        return static::$legacyMode ? static::findLegacy($id) : static::find($id);
    }
    
    protected static function findLegacy($id)
    {
        return static::find($id); // Fallback to modern method
    }
    
    public static function allUnified()
    {
        return static::$legacyMode ? static::allLegacy() : static::all();
    }
    
    protected static function allLegacy()
    {
        return static::all(); // Fallback to modern method
    }
    
    public function saveUnified()
    {
        return static::$legacyMode ? $this->saveLegacy() : $this->save();
    }
    
    protected function saveLegacy()
    {
        return $this->save(); // Fallback to modern method
    }
    
    public function deleteUnified()
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