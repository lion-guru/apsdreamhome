<?php
/**
 * EmailTemplateController
 * Admin UI for managing HTML email templates, with Test Send + Preview actions.
 *
 * Templates live in app/views/emails/ and are rendered by
 * App\Services\Communication\TemplateService::renderHtmlTemplate().
 */

namespace App\Http\Controllers\Admin;

use App\Services\Communication\TemplateService;
use App\Services\Communication\EmailService;
use Exception;

class EmailTemplateController extends AdminController
{
    private TemplateService $templateService;

    public function __construct()
    {
        parent::__construct();
        $this->templateService = new TemplateService();
    }

    /**
     * GET /admin/email-templates
     * Lists all HTML email templates in a table with Preview + Test Send buttons.
     */
    public function index()
    {
        $this->requireAdmin();

        $templates = $this->templateService->list();

        $this->data['page_title']       = 'Email Templates - APS Dream Home';
        $this->data['page_heading']     = 'HTML Email Templates';
        $this->data['templates']        = $templates;
        $this->data['flash_success']    = $_SESSION['flash_success'] ?? $_SESSION['success'] ?? null;
        $this->data['flash_error']      = $_SESSION['flash_error']   ?? $_SESSION['error']   ?? null;
        $this->data['admin_email']      = $this->getAdminEmail();
        $this->data['base_url']         = defined('BASE_URL') ? BASE_URL : '';

        // Clear flash after read
        unset($_SESSION['flash_success'], $_SESSION['success'],
              $_SESSION['flash_error'],   $_SESSION['error']);

        $this->render('admin/email_templates');
    }

    private function adminRedirect(string $url): void
    {
        $base = defined('BASE_URL') ? BASE_URL : '';
        if (!preg_match('#^https?://#i', $url)) {
            $url = rtrim($base, '/') . '/' . ltrim($url, '/');
        }
        header('Location: ' . $url);
        exit;
    }

    /**
     * GET /admin/email-templates/preview/{code}
     * Renders the template HTML for a given code, returned as HTML for inline display.
     */
    public function preview(string $code)
    {
        $this->requireAdmin();

        $result = $this->templateService->renderHtmlTemplate($code, []);

        if (!$result['ok']) {
            http_response_code(404);
            echo '<div class="style-48911">'
               . '<h2>Template not found</h2>'
               . '<p>' . htmlspecialchars($result['error'] ?? 'Unknown error') . '</p>'
               . '</div>';
            return;
        }

        // Echo the rendered HTML directly (this is the full HTML document)
        echo $result['html'];
    }

    /**
     * GET /admin/email-templates/test/{code}
     * Sends a test email using sample placeholder values to the admin's address.
     */
    public function test(string $code)
    {
        $this->requireAdmin();

        $result = $this->templateService->renderHtmlTemplate($code, []);

        if (!$result['ok']) {
            $_SESSION['flash_error'] = $result['error'] ?? 'Unknown template error';
            $this->adminRedirect('/admin/email-templates');
            return;
        }

        $toEmail = $this->getAdminEmail();
        if (empty($toEmail)) {
            $_SESSION['flash_error'] = 'No admin email configured. Set MAIL_FROM_ADDRESS in .env.';
            $this->adminRedirect('/admin/email-templates');
            return;
        }

        $subject = $result['subject'] ?? 'Test Email';
        $body    = $result['html']    ?? '';

        try {
            $emailService = new EmailService();
            $sent = $emailService->send($toEmail, $subject, $body);
        } catch (\Exception $e) {
            error_log('EmailTemplateController::test send failed: ' . $e->getMessage());
            $sent = false;
        }

        if ($sent) {
            $_SESSION['flash_success'] = "Test email sent to {$toEmail} (template: {$code}).";
        } else {
            $_SESSION['flash_error'] = "Failed to send test email. Check SMTP config in /admin/settings/email. (Template: {$code})";
        }

        $this->adminRedirect('/admin/email-templates');
    }

    /**
     * Get the admin's email - either from session or env settings.
     */
    private function getAdminEmail(): string
    {
        if (!empty($_SESSION['admin_email'])) {
            return $_SESSION['admin_email'];
        }
        if (!empty($_ENV['ADMIN_EMAIL'])) {
            return $_ENV['ADMIN_EMAIL'];
        }
        if (!empty($_ENV['MAIL_FROM_ADDRESS'])) {
            return $_ENV['MAIL_FROM_ADDRESS'];
        }
        try {
            $row = $this->db->fetchOne(
                "SELECT setting_value FROM app_settings WHERE setting_key = 'smtp_from_email' LIMIT 1"
            );
            if (!empty($row['setting_value'])) {
                return $row['setting_value'];
            }
        } catch (\Exception $e) {
        // ignore - fall through to empty string
        error_log($e->getMessage());
        }
        return '';
    }
}
