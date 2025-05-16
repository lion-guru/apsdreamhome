<?php
// SMS Template Management System

class SMSTemplateManager {
    // Dependencies
    private $db;
    private $logger;

    // Cached templates
    private $template_cache = [];

    public function __construct($db, $logger) {
        $this->db = $db;
        $this->logger = $logger;
    }

    /**
     * Get SMS template by name
     * @param string $template_name Template name
     * @return array|null Template details
     */
    public function getTemplate($template_name) {
        // Check cache first
        if (isset($this->template_cache[$template_name])) {
            return $this->template_cache[$template_name];
        }

        try {
            // Fetch template from database
            $stmt = $this->db->prepare("
                SELECT id, name, template, variables 
                FROM sms_templates 
                WHERE name = ? AND active = 1
            ");
            $stmt->bind_param('s', $template_name);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                $this->logger->log(
                    "SMS template not found: {$template_name}", 
                    'warning', 
                    'sms'
                );
                return null;
            }

            $template = $result->fetch_assoc();

            // Parse variables
            $template['variables'] = json_decode($template['variables'], true) ?? [];

            // Cache template
            $this->template_cache[$template_name] = $template;

            return $template;
        } catch (Exception $e) {
            $this->logger->log(
                "SMS template retrieval error: " . $e->getMessage(), 
                'error', 
                'sms'
            );
            return null;
        }
    }

    /**
     * Create or update SMS template
     * @param string $name Template name
     * @param string $template SMS template text
     * @param array $variables Template variables
     * @return bool
     */
    public function createOrUpdateTemplate($name, $template, $variables = []) {
        try {
            // Prepare SQL statement
            $stmt = $this->db->prepare("
                INSERT INTO sms_templates 
                (name, template, variables, active) 
                VALUES (?, ?, ?, 1) 
                ON DUPLICATE KEY UPDATE 
                template = ?, 
                variables = ?,
                updated_at = NOW()
            ");

            // Serialize variables
            $variables_json = json_encode($variables);

            $stmt->bind_param(
                'sssss', 
                $name, 
                $template, 
                $variables_json,
                $template, 
                $variables_json
            );

            $result = $stmt->execute();

            // Clear cache for this template
            unset($this->template_cache[$name]);

            // Log template creation/update
            $this->logger->log(
                "SMS template {$name} " . ($stmt->insert_id ? 'created' : 'updated'), 
                'info', 
                'sms'
            );

            return $result;
        } catch (Exception $e) {
            $this->logger->log(
                "SMS template creation error: " . $e->getMessage(), 
                'error', 
                'sms'
            );
            return false;
        }
    }

    /**
     * Render SMS template
     * @param string $template_name Template name
     * @param array $data Template replacement data
     * @return string|null Rendered template
     */
    public function renderTemplate($template_name, $data = []) {
        $template = $this->getTemplate($template_name);

        if (!$template) {
            return null;
        }

        // Validate required variables
        $missing_vars = array_diff($template['variables'], array_keys($data));
        if (!empty($missing_vars)) {
            $this->logger->log(
                "Missing template variables for {$template_name}: " . 
                implode(', ', $missing_vars), 
                'warning', 
                'sms'
            );
        }

        // Replace template variables
        $message = $template['template'];
        foreach ($data as $key => $value) {
            $message = str_replace("{{" . $key . "}}", $value, $message);
        }

        return $message;
    }

    /**
     * Activate or deactivate a template
     * @param string $template_name Template name
     * @param bool $active Whether to activate or deactivate
     * @return bool
     */
    public function setTemplateStatus($template_name, $active = true) {
        try {
            $stmt = $this->db->prepare("
                UPDATE sms_templates 
                SET active = ?, updated_at = NOW() 
                WHERE name = ?
            ");
            $active_int = $active ? 1 : 0;
            $stmt->bind_param('is', $active_int, $template_name);
            $result = $stmt->execute();

            // Clear cache
            unset($this->template_cache[$template_name]);

            // Log status change
            $this->logger->log(
                "SMS template {$template_name} " . 
                ($active ? 'activated' : 'deactivated'), 
                'info', 
                'sms'
            );

            return $result;
        } catch (Exception $e) {
            $this->logger->log(
                "SMS template status change error: " . $e->getMessage(), 
                'error', 
                'sms'
            );
            return false;
        }
    }

    /**
     * List all SMS templates
     * @param bool $active_only Only return active templates
     * @return array List of templates
     */
    public function listTemplates($active_only = true) {
        try {
            $query = "SELECT id, name, template, active FROM sms_templates";
            if ($active_only) {
                $query .= " WHERE active = 1";
            }

            $result = $this->db->query($query);
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            $this->logger->log(
                "SMS template listing error: " . $e->getMessage(), 
                'error', 
                'sms'
            );
            return [];
        }
    }

    /**
     * Manage user SMS notification preferences
     * @param int $user_id User ID
     * @param bool $enable_sms Enable/disable SMS notifications
     * @param array $notification_types Enabled notification types
     * @return bool
     */
    public function updateUserSMSPreferences($user_id, $enable_sms = true, $notification_types = []) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO user_sms_preferences 
                (user_id, enable_sms, notification_types) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                enable_sms = ?, 
                notification_types = ?
            ");

            $notification_types_json = json_encode($notification_types);

            $stmt->bind_param(
                'issss', 
                $user_id, 
                $enable_sms, 
                $notification_types_json,
                $enable_sms, 
                $notification_types_json
            );

            $result = $stmt->execute();

            // Log preference update
            $this->logger->log(
                "SMS preferences updated for user {$user_id}", 
                'info', 
                'sms'
            );

            return $result;
        } catch (Exception $e) {
            $this->logger->log(
                "SMS preference update error: " . $e->getMessage(), 
                'error', 
                'sms'
            );
            return false;
        }
    }

    /**
     * Get user SMS notification preferences
     * @param int $user_id User ID
     * @return array|null User SMS preferences
     */
    public function getUserSMSPreferences($user_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT enable_sms, notification_types 
                FROM user_sms_preferences 
                WHERE user_id = ?
            ");
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                // Default preferences
                return [
                    'enable_sms' => true,
                    'notification_types' => []
                ];
            }

            $preferences = $result->fetch_assoc();
            $preferences['notification_types'] = 
                json_decode($preferences['notification_types'], true) ?? [];

            return $preferences;
        } catch (Exception $e) {
            $this->logger->log(
                "SMS preference retrieval error: " . $e->getMessage(), 
                'error', 
                'sms'
            );
            return null;
        }
    }
}

// Helper function for dependency injection
function getSMSTemplateManager() {
    $container = container(); // Assuming dependency container is loaded
    
    // Lazy load dependencies
    $db = $container->resolve('db_connection');
    $logger = $container->resolve('logger');
    
    return new SMSTemplateManager($db, $logger);
}

return getSMSTemplateManager();
