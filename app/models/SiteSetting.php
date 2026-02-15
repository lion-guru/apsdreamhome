<?php

namespace App\Models;

use App\Core\UnifiedModel;

class SiteSetting extends UnifiedModel
{
    public static $table = 'site_settings';
    public static $primaryKey = 'id';

    protected array $fillable = [
        'setting_name',
        'value'
    ];

    /**
     * Get setting value by name
     */
    public static function getByName($name, $default = null)
    {
        $setting = self::where('setting_name', $name)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Get multiple settings by names
     */
    public static function getByNames(array $names)
    {
        if (empty($names)) return [];

        $settings = static::query()
            ->select(['setting_name', 'value'])
            ->from(self::$table)
            ->whereIn('setting_name', $names)
            ->get();

        $results = [];
        foreach ($settings as $row) {
            $results[$row['setting_name']] = $row['value'];
        }

        return $results;
    }
}
