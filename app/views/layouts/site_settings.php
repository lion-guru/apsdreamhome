<?php
/**
 * Site Settings Management
 * Functions for managing site-wide settings
 */

/**
 * Get site setting value
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function getSiteSetting($key, $default = null) {
    static $settings = null;

    if ($settings === null) {
        $settings = [];

        // Load settings from database if available
        try {
            if (function_exists('getDbConnection')) {
                $pdo = getDbConnection();
                $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
                if ($stmt) {
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $settings[$row['setting_key']] = $row['setting_value'];
                    }
                }
            }
        } catch (Exception $e) {
            // Settings not available, use defaults
        }

        // Set default settings if not in database
        $default_settings = [
            'site_title' => 'APS Dream Homes Pvt Ltd',
            'site_description' => 'APS Dream Homes Pvt Ltd - Leading real estate developer in Gorakhpur with 8+ years of excellence',
            'site_keywords' => 'real estate, property, Gorakhpur, apartments, villas, plots, commercial',
            'contact_phone' => '+91-7007444842',
            'contact_email' => 'info@apsdreamhome.com',
            'contact_address' => 'Gorakhpur, Uttar Pradesh, India',
            'logo_path' => '',
            'social_facebook' => '',
            'social_twitter' => '',
            'social_instagram' => '',
            'social_linkedin' => '',
            'social_youtube' => ''
        ];

        foreach ($default_settings as $setting_key => $setting_value) {
            if (!isset($settings[$setting_key])) {
                $settings[$setting_key] = $setting_value;
            }
        }
    }

    return $settings[$key] ?? $default;
}

/**
 * Set site setting value
 * @param string $key
 * @param mixed $value
 * @return bool
 */
function setSiteSetting($key, $value) {
    try {
        if (function_exists('getDbConnection')) {
            $pdo = getDbConnection();

            $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value, updated_at) VALUES (?, ?, NOW())
                                  ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()");
            return $stmt->execute([$key, $value, $value]);
        }
        return false;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Get all site settings
 * @return array
 */
function getAllSiteSettings() {
    return getSiteSetting(null, []);
}
?>
