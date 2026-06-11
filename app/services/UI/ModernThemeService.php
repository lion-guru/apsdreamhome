<?php

namespace App\Services\UI;

use App\Core\Database\Database;

/**
 * Modern Theme & UI Service
 * Dark mode, customizable themes, and modern UI components
 */
class ModernThemeService
{
    private $database;
    
    // Predefined theme presets
    private $themes = [
        'light' => [
            'name' => 'Light',
            'primary' => '#3b82f6',
            'secondary' => '#64748b',
            'background' => '#ffffff',
            'surface' => '#f8fafc',
            'text' => '#1e293b',
            'textSecondary' => '#64748b',
            'border' => '#e2e8f0',
            'success' => '#10b981',
            'warning' => '#f59e0b',
            'error' => '#ef4444',
            'info' => '#3b82f6'
        ],
        'dark' => [
            'name' => 'Dark',
            'primary' => '#60a5fa',
            'secondary' => '#94a3b8',
            'background' => '#0f172a',
            'surface' => '#1e293b',
            'text' => '#f8fafc',
            'textSecondary' => '#94a3b8',
            'border' => '#334155',
            'success' => '#34d399',
            'warning' => '#fbbf24',
            'error' => '#f87171',
            'info' => '#60a5fa'
        ],
        'navy' => [
            'name' => 'Navy Blue',
            'primary' => '#3b82f6',
            'secondary' => '#6366f1',
            'background' => '#1e3a5f',
            'surface' => '#2a4a6f',
            'text' => '#ffffff',
            'textSecondary' => '#a0c4e8',
            'border' => '#3d5a80',
            'success' => '#22c55e',
            'warning' => '#f59e0b',
            'error' => '#ef4444',
            'info' => '#60a5fa'
        ],
        'purple' => [
            'name' => 'Royal Purple',
            'primary' => '#8b5cf6',
            'secondary' => '#a78bfa',
            'background' => '#2e1065',
            'surface' => '#4c1d95',
            'text' => '#ffffff',
            'textSecondary' => '#c4b5fd',
            'border' => '#5b21b6',
            'success' => '#22c55e',
            'warning' => '#f59e0b',
            'error' => '#ef4444',
            'info' => '#60a5fa'
        ]
    ];
    
    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->ensureTablesExist();
    }
    
    /**
     * Ensure tables exist
     */
    private function ensureTablesExist(): void
    {
        $pdo = $this->database->getConnection();
        
        // User preferences table
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Quick actions table
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Dashboard widgets
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
    }
    
    /**
     * Get theme for user
     */
    public function getUserTheme(int $userId, string $userType = 'admin'): array
    {
        $sql = "SELECT * FROM user_theme_preferences WHERE user_id = ? AND user_type = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$userId, $userType]);
        $prefs = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$prefs) {
            // Create default preferences
            $this->setDefaultPreferences($userId, $userType);
            $prefs = [
                'theme_preset' => 'light',
                'is_dark_mode' => 0,
                'sidebar_collapsed' => 0,
                'compact_mode' => 0
            ];
        }
        
        $themeKey = $prefs['is_dark_mode'] ? 'dark' : ($prefs['theme_preset'] ?: 'light');
        $theme = $this->themes[$themeKey] ?? $this->themes['light'];
        
        // Apply custom colors if set
        if ($prefs['custom_colors']) {
            $custom = json_decode($prefs['custom_colors'], true);
            $theme = array_merge($theme, $custom);
        }
        
        return [
            'theme' => $theme,
            'preferences' => $prefs
        ];
    }
    
    /**
     * Set default preferences
     */
    private function setDefaultPreferences(int $userId, string $userType): void
    {
        $sql = "INSERT IGNORE INTO user_theme_preferences 
            (user_id, user_type, theme_preset) 
            VALUES (?, ?, 'light')";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$userId, $userType]);
        
        // Add default quick actions for admin
        if ($userType === 'admin') {
            $this->addDefaultQuickActions($userId, $userType);
        }
    }
    
    /**
     * Add default quick actions
     */
    private function addDefaultQuickActions(int $userId, string $userType): void
    {
        $defaultActions = [
            ['name' => 'Add Property', 'icon' => 'fa-plus', 'url' => '/admin/properties/create', 'order' => 1],
            ['name' => 'New Lead', 'icon' => 'fa-user-plus', 'url' => '/admin/leads/create', 'order' => 2],
            ['name' => 'Add Booking', 'icon' => 'fa-handshake', 'url' => '/admin/bookings/create', 'order' => 3],
            ['name' => 'Quick Search', 'icon' => 'fa-search', 'url' => '/admin/properties', 'order' => 4],
            ['name' => 'Reports', 'icon' => 'fa-chart-bar', 'url' => '/admin/reports', 'order' => 5],
            ['name' => 'Site Visits', 'icon' => 'fa-calendar-check', 'url' => '/admin/workflows', 'order' => 6],
        ];
        
        $sql = "INSERT IGNORE INTO user_quick_actions 
            (user_id, user_type, action_name, action_icon, action_url, display_order) 
            VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->database->prepare($sql);
        
        foreach ($defaultActions as $action) {
            $stmt->execute([
                $userId, 
                $userType, 
                $action['name'],
                $action['icon'],
                $action['url'],
                $action['order']
            ]);
        }
    }
    
    /**
     * Toggle dark mode
     */
    public function toggleDarkMode(int $userId, string $userType = 'admin'): array
    {
        // Get current
        $sql = "SELECT is_dark_mode FROM user_theme_preferences WHERE user_id = ? AND user_type = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$userId, $userType]);
        $current = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        $newMode = !($current['is_dark_mode'] ?? false);
        
        // Update
        $updateSql = "INSERT INTO user_theme_preferences 
            (user_id, user_type, is_dark_mode, theme_preset) 
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            is_dark_mode = VALUES(is_dark_mode)";
        
        $updateStmt = $this->database->prepare($updateSql);
        $updateStmt->execute([$userId, $userType, $newMode ? 1 : 0, $newMode ? 'dark' : 'light']);
        
        return [
            'success' => true,
            'dark_mode' => $newMode,
            'theme' => $newMode ? $this->themes['dark'] : $this->themes['light']
        ];
    }
    
    /**
     * Get quick actions
     */
    public function getQuickActions(int $userId, string $userType = 'admin'): array
    {
        $sql = "SELECT * FROM user_quick_actions 
            WHERE user_id = ? AND user_type = ? AND is_active = 1 
            ORDER BY display_order ASC";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$userId, $userType]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Add quick action
     */
    public function addQuickAction(int $userId, string $userType, array $action): array
    {
        try {
            $sql = "INSERT INTO user_quick_actions 
                (user_id, user_type, action_name, action_icon, action_url, display_order) 
                VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->database->prepare($sql);
            $stmt->execute([
                $userId,
                $userType,
                $action['name'],
                $action['icon'] ?? 'fa-link',
                $action['url'],
                $action['order'] ?? 99
            ]);
            
            return [
                'success' => true,
                'action_id' => $this->database->lastInsertId()
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Update quick action order
     */
    public function reorderQuickActions(int $userId, string $userType, array $order): array
    {
        try {
            $sql = "UPDATE user_quick_actions SET display_order = ? WHERE id = ? AND user_id = ? AND user_type = ?";
            $stmt = $this->database->prepare($sql);
            
            foreach ($order as $index => $actionId) {
                $stmt->execute([$index, $actionId, $userId, $userType]);
            }
            
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Remove quick action
     */
    public function removeQuickAction(int $actionId, int $userId, string $userType): array
    {
        $sql = "DELETE FROM user_quick_actions WHERE id = ? AND user_id = ? AND user_type = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$actionId, $userId, $userType]);
        
        return [
            'success' => $stmt->rowCount() > 0,
            'message' => $stmt->rowCount() > 0 ? 'Action removed' : 'Not found'
        ];
    }
    
    /**
     * Get available themes
     */
    public function getAvailableThemes(): array
    {
        return $this->themes;
    }
    
    /**
     * Set theme
     */
    public function setTheme(int $userId, string $userType, string $themeKey, bool $isDark = false): array
    {
        if (!isset($this->themes[$themeKey]) && !in_array($themeKey, ['light', 'dark'])) {
            return ['success' => false, 'error' => 'Invalid theme'];
        }
        
        $sql = "INSERT INTO user_theme_preferences 
            (user_id, user_type, theme_preset, is_dark_mode) 
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            theme_preset = VALUES(theme_preset),
            is_dark_mode = VALUES(is_dark_mode)";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$userId, $userType, $themeKey, $isDark ? 1 : 0]);
        
        return [
            'success' => true,
            'theme' => $this->themes[$themeKey] ?? $this->themes['light']
        ];
    }
    
    /**
     * Generate CSS variables
     */
    public function generateCSSVariables(array $theme): string
    {
        $css = ":root {\n";
        foreach ($theme as $key => $value) {
            $css .= "  --theme-{$key}: {$value};\n";
        }
        $css .= "}\n";
        
        // Dark mode specific styles
        if (isset($theme['background']) && $this->isDarkColor($theme['background'])) {
            $css .= $this->getDarkModeStyles();
        }
        
        return $css;
    }
    
    /**
     * Check if color is dark
     */
    private function isDarkColor(string $color): bool
    {
        // Simple check - if color starts with dark hex codes
        $darkPrefixes = ['#0', '#1', '#2'];
        foreach ($darkPrefixes as $prefix) {
            if (strpos($color, $prefix) === 0) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Get dark mode specific styles
     */
    private function getDarkModeStyles(): string
    {
        return '
/* Dark Mode Overrides */
.dark-mode .card { background-color: var(--theme-surface); border-color: var(--theme-border); }
.dark-mode .table { color: var(--theme-text); }
.dark-mode .form-control { background-color: var(--theme-surface); color: var(--theme-text); border-color: var(--theme-border); }
.dark-mode .modal-content { background-color: var(--theme-surface); color: var(--theme-text); }
.dark-mode .dropdown-menu { background-color: var(--theme-surface); border-color: var(--theme-border); }
.dark-mode .dropdown-item { color: var(--theme-text); }
.dark-mode .dropdown-item:hover { background-color: var(--theme-primary); color: #fff; }
';
    }
}
