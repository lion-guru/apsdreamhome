<?php

namespace App\Services\Legacy;
// Email Template Management System

use App\Traits\ServiceTenantTrait;

class EmailTemplateManager {
    use ServiceTenantTrait;

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
                WHERE name = ? AND active = 1" . $this->tenantSql(),
            [$template_name]);

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
        } catch (\Exception $e) {
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
            $tid = $this->tenantId();
            $variables_json = json_encode($variables);

            /* Build INSERT with optional tenant_id */
            $columns = "name, subject, body, variables, active";
            $values = "?, ?, ?, ?, 1";
            $params = [$name, $subject, $body, $variables_json, $subject, $body, $variables_json];
            if ($tid > 1) {
                $columns .= ", tenant_id";
                $values .= ", ?";
                $params[] = $tid;
            }

            $sql = "
                INSERT INTO email_templates
                ($columns)
                VALUES ($values)
                ON DUPLICATE KEY UPDATE
                subject = VALUES(subject),
                body = VALUES(body),
                variables = VALUES(variables),
                updated_at = NOW()
            ";

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
        } catch (\Exception $e) {
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
                "UPDATE email_templates SET is_active = ?, updated_at = NOW() WHERE template_name = ?" . $this->tenantSql(),
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
        } catch (\Exception $e) {
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
            $tidClause = $this->tenantSql();
            if ($active_only) {
                $query .= " WHERE active = 1" . $tidClause;
            } else {
                $query .= $tidClause;
                if ($tidClause) {
                    $query .= '';
                }
            }

            return $this->db->fetchAll($query, $params);
        } catch (\Exception $e) {
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

if (function_exists('container') && !class_exists('App\\Services\\Communication\\TemplateService', false)) {
    return getEmailTemplateManager();
}

// =====================================================================
// Modern TemplateService (App\Services\Communication\TemplateService)
// Renders HTML email templates from app/views/emails/*.php
// =====================================================================

namespace App\Services\Communication;

/**
 * TemplateService
 * Loads HTML email templates from app/views/emails/ and replaces
 * {{var}} placeholders with values. Returns the rendered HTML string.
 */
class TemplateService
{
    /** @var string Absolute path to the email templates directory */
    private string $templatesPath;

    /** @var array In-memory cache of loaded templates */
    private array $cache = [];

    /** Catalog of supported templates (code => [subject, file]) */
    public const CATALOG = [
        'welcome' => [
            'subject' => 'Welcome to APS Dream Home - Your Dream Property Awaits!',
            'file'    => 'welcome.php',
        ],
        'password_reset' => [
            'subject' => 'Reset Your Password - APS Dream Home',
            'file'    => 'password_reset.php',
        ],
        'booking_confirmation' => [
            'subject' => 'Booking Confirmed - APS Dream Home',
            'file'    => 'booking_confirmation.php',
        ],
        'property_approved' => [
            'subject' => 'Your Property Has Been Approved! - APS Dream Home',
            'file'    => 'property_approved.php',
        ],
    ];

    /**
     * Default placeholder values for a given template code.
     * Computed at call-time so dynamic values (year, date) stay fresh.
     */
    private function defaultsFor(string $code): array
    {
        $year = date('Y');
        // Shared contact info from DB settings
        $contact = [
            'company_phone' => '+91 92771 21112',
            'company_email' => 'info@apsdreamhome.com',
            'company_name'  => 'APS Dream Home',
        ];
        try {
            $settings = \App\Services\SiteContentService::getInstance()->getSection('settings');
            $contact['company_phone'] = $settings['contact_phone'] ?? $contact['company_phone'];
            $contact['company_email'] = $settings['contact_email'] ?? $contact['company_email'];
            $contact['company_name']  = $settings['company_name'] ?? $contact['company_name'];
        } catch (\Throwable $e) { error_log($e->getMessage()); }
        switch ($code) {
            case 'welcome':
                return array_merge($contact, [
                    'name'             => 'Customer',
                    'login_url'        => '/login',
                    'logo_url'         => '/assets/images/logo.png',
                    'unsubscribe_url'  => '/unsubscribe',
                    'preferences_url'  => '/email-preferences',
                    'year'             => $year,
                ]);
            case 'password_reset':
                return array_merge($contact, [
                    'user_name'  => 'Customer',
                    'reset_url'  => '/reset-password?token=abc',
                    'expires_in' => '1 hour',
                    'year'       => $year,
                ]);
            case 'booking_confirmation':
                return array_merge($contact, [
                    'customer_name'     => 'Customer',
                    'booking_id'        => 'BK-0000',
                    'property_name'     => 'Your Property',
                    'property_location' => 'Gorakhpur, UP',
                    'booking_date'      => date('d M Y'),
                    'amount'            => '0',
                    'booking_url'       => '/user/bookings',
                    'unsubscribe_url'   => '/unsubscribe',
                    'year'              => $year,
                ]);
            case 'property_approved':
                return array_merge($contact, [
                    'user_name'         => 'Customer',
                    'property_name'     => 'Your Property',
                    'property_location' => 'Gorakhpur, UP',
                    'property_type'     => 'Plot',
                    'property_area'     => '1000',
                    'property_price'    => '25,00,000',
                    'property_image'    => '/assets/images/property-placeholder.jpg',
                    'property_url'      => '/properties',
                    'unsubscribe_url'   => '/unsubscribe',
                    'year'              => $year,
                ]);
            default:
                return array_merge($contact, ['year' => $year]);
        }
    }

    public function __construct(?string $templatesPath = null)
    {
        $this->templatesPath = $templatesPath
            ?? (__DIR__ . '/../../views/emails');

        if (!is_dir($this->templatesPath)) {
            error_log('TemplateService: emails directory missing at ' . $this->templatesPath);
        }
    }

    /**
     * List all available template codes + metadata.
     */
    public function list(): array
    {
        $out = [];
        foreach (self::CATALOG as $code => $meta) {
            $filePath = $this->templatesPath . '/' . $meta['file'];
            $out[] = [
                'code'    => $code,
                'subject' => $meta['subject'],
                'file'    => $meta['file'],
                'exists'  => is_file($filePath),
                'size'    => is_file($filePath) ? filesize($filePath) : 0,
                'vars'    => array_keys($this->defaultsFor($code)),
            ];
        }
        return $out;
    }

    /**
     * Render an HTML email template by code.
     *
     * @param string $code One of the codes defined in CATALOG
     * @param array  $vars Associative array of {{var}} => value
     * @return array{ok:bool, html?:string, subject?:string, error?:string, file?:string}
     */
    public function renderHtmlTemplate(string $code, array $vars = []): array
    {
        $code = preg_replace('/[^a-z0-9_]/', '', strtolower($code)) ?? '';

        if (!isset(self::CATALOG[$code])) {
            return ['ok' => false, 'error' => "Unknown template code: {$code}"];
        }

        $meta    = self::CATALOG[$code];
        $file    = $this->templatesPath . '/' . $meta['file'];
        $subject = $meta['subject'];

        if (!is_file($file)) {
            return ['ok' => false, 'error' => "Template file not found: {$file}", 'file' => $meta['file']];
        }

        // Merge defaults with caller-supplied vars (caller wins)
        $merged = array_merge($this->defaultsFor($code), $vars);

        // Build the HTML. Use a buffered include to capture the rendered file
        // then run the placeholder replacement. ob_start/ob_get_clean also
        // lets templates echo dynamic content if needed in the future.
        $html = $this->loadFromCache($code, $file);
        if ($html === null) {
            $html = (string)file_get_contents($file);
            $this->cache[$code] = $html;
        }

        $html = $this->replaceVars($html, $merged);

        // Replace subject placeholders too
        $resolvedSubject = $this->replaceVars($subject, $merged);

        return [
            'ok'      => true,
            'html'    => $html,
            'subject' => $resolvedSubject,
            'file'    => $meta['file'],
        ];
    }

    /**
     * Replace {{var}} (or {{ var }}) in the HTML with provided values.
     * HTML-escapes values by default to prevent XSS via template injection.
     */
    private function replaceVars(string $html, array $vars): string
    {
        foreach ($vars as $key => $value) {
            $replacement = htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            // Match {{key}} with optional whitespace
            $html = preg_replace(
                '/\{\{\s*' . preg_quote((string)$key, '/') . '\s*\}\}/i',
                $replacement,
                $html
            );
        }
        return $html;
    }

    /**
     * Helper: returns the rendered HTML only (or empty string on failure).
     */
    public function render(string $code, array $vars = []): string
    {
        $result = $this->renderHtmlTemplate($code, $vars);
        return $result['ok'] ? ($result['html'] ?? '') : '';
    }

    private function loadFromCache(string $code, string $file): ?string
    {
        return $this->cache[$code] ?? null;
    }

    public function clearCache(): void
    {
        $this->cache = [];
    }
}
