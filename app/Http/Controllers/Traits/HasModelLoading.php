<?php

namespace App\Http\Controllers\Traits;

/**
 * Trait HasModelLoading
 *
 * Provides lazy model loading and common utility methods.
 */
trait HasModelLoading
{
    /**
     * Cached model instances.
     */
    protected array $models = [];

    /**
     * Get model instance (lazy loading).
     */
    protected function model(string $name)
    {
        if (!isset($this->models[$name])) {
            $class = "App\\Models\\{$name}";
            $this->models[$name] = new $class();
        }
        return $this->models[$name];
    }

    /**
     * Load model (alias for model).
     */
    protected function modelLocal(string $modelName)
    {
        return $this->model($modelName);
    }

    /**
     * Get the current logged in user.
     */
    protected function getCurrentUser()
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        $userId = $_SESSION['user_id'];
        $userModel = $this->model('User');
        return $userModel->getUserById($userId);
    }

    /**
     * Log a lead activity.
     */
    protected function logLeadActivity(int $leadId, string $type, string $description, array $metadata = []): bool
    {
        try {
            $db = \App\Core\Database\Database::getInstance();
            $sql = "INSERT INTO lead_activities (lead_id, activity_type, description, metadata, created_by, created_at)
                    VALUES (?, ?, ?, ?, ?, NOW())";
            $db->execute($sql, [
                $leadId,
                $type,
                $description,
                json_encode($metadata),
                $_SESSION['user_id'] ?? null
            ]);
            return true;
        } catch (\Throwable $e) {
            error_log("Failed to log lead activity: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Log activity.
     */
    protected function logActivity(string $action, string $details = ''): void
    {
        try {
            $userId = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? null;
            if (!$userId) return;

            $db = \App\Core\Database\Database::getInstance();
            $sql = "INSERT INTO activity_logs_unified (user_id, action, details, created_at) VALUES (?, ?, ?, NOW())";
            $db->execute($sql, [$userId, $action, $details]);
        } catch (\Throwable $e) {
            error_log("Failed to log activity: " . $e->getMessage());
        }
    }

    /**
     * Get configuration value.
     */
    protected function getConfig(string $key, $default = null)
    {
        $config = [
            'app_name' => 'APS Dream Home',
            'app_url' => rtrim(BASE_URL, '/'),
            'timezone' => 'Asia/Kolkata'
        ];

        return $config[$key] ?? $default;
    }

    /**
     * Check if user has a specific role.
     */
    protected function hasRole(string $role): bool
    {
        return (\App\Http\Middleware\RBACManager::getUserRole() ?? '') === $role;
    }

    /**
     * Start performance monitoring.
     */
    protected function startPerformanceMonitoring(): void
    {
        $this->start_time = microtime(true);
    }

    /**
     * End performance monitoring.
     */
    protected function endPerformanceMonitoring(): void
    {
        if (isset($this->start_time)) {
            $end_time = microtime(true);
            $execution_time = $end_time - $this->start_time;
            if (defined('DEBUG_MODE') && constant('DEBUG_MODE')) {
                error_log("Page execution time: " . number_format($execution_time, 4) . " seconds");
            }
        }
    }

    /**
     * Go back to previous page.
     */
    protected function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        header("Location: $referer");
        exit();
    }
}