<?php

/**
 * Settings Helper
 * Provides functions to retrieve system settings from the database.
 */

require_once dirname(__DIR__) . '/core/init.php';

/**
 * Get a system setting by its key.
 *
 * @param string $key The setting key to retrieve.
 * @param mixed $default Default value if the setting is not found.
 * @return mixed The setting value or default.
 */
function getSystemSetting($key, $default = null)
{
    static $settings_cache = [];

    // Check cache first
    if (isset($settings_cache[$key])) {
        return $settings_cache[$key];
    }

    try {
        $db = \App\Core\App::database();
        $row = $db->fetch("SELECT setting_value FROM system_settings WHERE setting_key = :key LIMIT 1", ['key' => $key]);

        if ($row) {
            $value = $row['setting_value'];
            $settings_cache[$key] = $value;
            return $value;
        }
    } catch (Exception $e) {
        error_log("Error in getSystemSetting: " . $e->getMessage());
    }

    return $default;
}

/**
 * Get all settings for a specific group.
 *
 * @param string $group The setting group to retrieve.
 * @return array Array of settings in the group.
 */
function getSettingsByGroup($group)
{
    try {
        $db = \App\Core\App::database();
        $rows = $db->fetchAll("SELECT setting_key, setting_value FROM system_settings WHERE setting_group = :group", ['group' => $group]);

        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    } catch (Exception $e) {
        error_log("Error in getSettingsByGroup: " . $e->getMessage());
        return [];
    }
}
