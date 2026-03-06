<?php

namespace App\Services\Legacy;
// Email Template Management System

class EmailTemplateManager {
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
     * Get email template by name
     * @param string $template_name Template name
     * @return array|null Template details
     */
    public function getTemplate($template_name) {
        // Check cache first
        if (isset($this->template_cache[$template_name])) {
            return $this->template_cache[$template_name];
        }

        try {
            // Fetch template from database using modern PDO-based API
            $template = $this->db->fetchOne("
                SELECT id, name, subject, body, variables
                FROM email_templates
                WHERE name = ? AND active = 1
            ", [$template_name]);

            if (!$template) {
                $this->logger->log(
                    "Email template not found: {$template_name}",
                    'warning',
                    'email'
                );
                return null;
            }

            // Parse variables
            $template['variables'] = json_decode($template['variables'], true) ?? [];

            // Cache template
            $this->template_cache[$template_name] = $template;

            return $template;
        } catch (Exception $e) {
            $this->logger->log(
                "Email template retrieval error: " . $e->getMessage(),
                'error',
                'email'
            );
            return null;
        }
    }

    /**
     * Create or update email template
     * @param string $name Template name
     * @param string $subject Email subject
     * @param string $body Email body
     * @param array $variables Template variables
     * @return bool
     */
    public function createOrUpdateTemplate($name, $subject, $body, $variables = []) {
        try {
            // Serialize variables
            $variables_json = json_encode($variables);

            // Execute using modern PDO-based API
            $sql = "
                INSERT INTO email_templates
                (name, subject, body, variables, active)
                VALUES (?, ?, ?, ?, 1)
                ON DUPLICATE KEY UPDATE
                subject = ?,
                body = ?,
                variables = ?,
                updated_at = NOW()
            ";

            $params = [
                $name,
                $subject,
                $body,
                $variables_json,
                $subject,
                $body,
                $variables_json
            ];

            return $this->db->executeQuery($sql, $params);

            // Clear cache for this template
            unset($this->template_cache[$name]);

            // Log template creation/update
            $this->logger->log(
                "Email template {$name} " . ($stmt->insert_id ? 'created' : 'updated'),
                'info',
                'email'
            );

            return $result;
        } catch (Exception $e) {
            $this->logger->log(
                "Email template creation error: " . $e->getMessage(),
                'error',
                'email'
            );
            return false;
        }
    }

    /**
     * Render email template
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
                'email'
            );
        }

        // Replace template variables
        $body = $template['body'];
        foreach ($data as $key => $value) {
            $body = str_replace("{{" . $key . "}}", h($value), $body);
        }

        return [
            'subject' => $template['subject'],
            'body' => $body
        ];
    }

    /**
     * Activate or deactivate a template
     * @param string $template_name Template name
     * @param bool $active Whether to activate or deactivate
     * @return bool
     */
    public function setTemplateStatus($template_name, $active = true) {
        try {
            $active_int = $active ? 1 : 0;
            $result = $this->db->executeQuery(
                "UPDATE email_templates SET active = ?, updated_at = NOW() WHERE name = ?",
                [$active_int, $template_name]
            );

            // Clear cache
            unset($this->template_cache[$template_name]);

            // Log status change
            $this->logger->log(
                "Email template {$template_name} " .
                ($active ? 'activated' : 'deactivated'),
                'info',
                'email'
            );

            return $result;
        } catch (Exception $e) {
            $this->logger->log(
                "Email template status change error: " . $e->getMessage(),
                'error',
                'email'
            );
            return false;
        }
    }

    /**
     * List all email templates
     * @param bool $active_only Only return active templates
     * @return array List of templates
     */
    public function listTemplates($active_only = true) {
        try {
            $query = "SELECT id, name, subject, active FROM email_templates";
            $params = [];
            if ($active_only) {
                $query .= " WHERE active = 1";
            }

            return $this->db->fetchAll($query, $params);
        } catch (Exception $e) {
            $this->logger->log(
                "Email template listing error: " . $e->getMessage(),
                'error',
                'email'
            );
            return [];
        }
    }
}

// Helper function for dependency injection
function getEmailTemplateManager() {
    $container = container(); // Assuming dependency container is loaded

    // Lazy load dependencies
    $db = $container->resolve('db_connection');
    $logger = $container->resolve('logger');

    return new EmailTemplateManager($db, $logger);
}

return getEmailTemplateManager();
