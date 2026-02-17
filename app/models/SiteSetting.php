<?php

namespace App\Models;

use App\Core\UnifiedModel;

class SiteSetting extends UnifiedModel
{
    public static $table = 'site_settings';
    public static $primaryKey = 'id';

    protected array $fillable = [
        'setting_name',
        'setting_value'
    ];

    /**
     * Get setting value by name
     */
    public static function getByName($name, $default = null)
    {
        $setting = self::where('setting_name', $name)->first();
        return $setting ? $setting->setting_value : $default;
    }

    /**
     * Get all settings
     */
    public static function getAllSettings()
    {
        try {
            $settings = static::query()
                ->select(['setting_name', 'setting_value'])
                ->from(self::$table)
                ->orderBy('setting_name')
                ->get();

            $results = [];
            foreach ($settings as $row) {
                // Handle both object and array return types
                $item = is_object($row) ? (array)$row : $row;
                $results[$item['setting_name']] = $item;
            }
            return $results;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get multiple settings by names
     */
    public static function getByNames(array $names)
    {
        if (empty($names)) return [];

        $settings = static::query()
            ->select(['setting_name', 'setting_value'])
            ->from(self::$table)
            ->whereIn('setting_name', $names)
            ->get();

        $results = [];
        foreach ($settings as $row) {
            $item = is_object($row) ? (array)$row : $row;
            $results[$item['setting_name']] = $item['setting_value'];
        }

        return $results;
    }
}
