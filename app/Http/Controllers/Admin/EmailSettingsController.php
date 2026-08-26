<?php
/**
 * Email Settings Controller
 * Admin can configure SMTP settings here
 * NOTE: Writes target app_settings (global config) — table does NOT exist / is cross-tenant config (not tenant business data). smtp_* keys are global platform settings shared across tenants. Intentionally NOT tenant-scoped. If per-tenant email config is needed in future, migrate to service_configs (which IS tenant-scoped).
 */

namespace App\Http\Controllers\Admin;

use App\Core\Database\Database;
use App\Services\Communication\EmailService;

class EmailSettingsController extends AdminController
{
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
    }
    
    /**
     * Show SMTP settings page (dedicated)
     */
    public function smtpSettings()
    {
        @session_start();

        if (!isset($_SESSION['admin_id'])) {
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }

        $smtp = $this->getSmtpFromDb();
        $base = BASE_URL;
        include __DIR__ . '/../../../views/admin/email/smtp-settings.php';
    }

    /**
     * Save SMTP settings to app_settings table
     */
    public function saveSmtp()
    {
        @session_start();

        if (!isset($_SESSION['admin_id'])) {
            $_SESSION['error'] = 'Unauthorized';
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }

        $settings = [
            'smtp_host' => $_POST['smtp_host'] ?? '',
            'smtp_port' => $_POST['smtp_port'] ?? '587',
            'smtp_username' => $_POST['smtp_username'] ?? '',
            'smtp_password' => $_POST['smtp_password'] ?? '',
            'smtp_encryption' => $_POST['smtp_encryption'] ?? 'tls',
            'smtp_from_email' => $_POST['smtp_from_email'] ?? '',
            'smtp_from_name' => $_POST['smtp_from_name'] ?? 'APS Dream Home',
        ];

        // Validate CSRF
        $token = $_POST['csrf_token'] ?? '';
        if (!\App\Helpers\SecurityHelper::validateCsrfToken($token)) {
            $_SESSION['error'] = 'Security token expired. Please try again.';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/admin/settings/smtp'));
            exit;
        }

        $db = $this->db;
        foreach ($settings as $key => $value) {
            $stmt = $db->prepare(
                "INSERT INTO app_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?"
            );
            $stmt->execute([$key, $value, $value]);
        }

        $_SESSION['success'] = 'SMTP settings saved successfully!';
        header('Location: ' . BASE_URL . '/admin/settings/smtp');
        exit;
    }

    /**
     * Fetch SMTP settings from app_settings table
     */
    private function getSmtpFromDb()
    {
        $db = $this->db;
        try {
            $rows = $db->fetchAll("SELECT setting_key, setting_value FROM app_settings WHERE setting_key LIKE 'smtp_%'");
            $settings = [];
            foreach ($rows as $row) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
            return $settings;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Show email settings page
     */
    public function index()
    {
        @session_start();
        
        // Check admin auth
        if (!isset($_SESSION['admin_id'])) {
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }
        
        $settings = $this->getSettings();
        $stats = $this->getEmailStats();
        $logs = $this->getRecentLogs();
        
        $base = BASE_URL;
        include __DIR__ . '/../../../views/admin/settings/email.php';
    }
    
    /**
     * Save email settings
     */
    public function save()
    {
        @session_start();
        
        if (!isset($_SESSION['admin_id'])) {
            $_SESSION['error'] = "Unauthorized";
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }
        
        $settings = [
            'MAIL_MAILER' => $_POST['mail_mailer'] ?? 'smtp',
            'MAIL_HOST' => $_POST['mail_host'] ?? '',
            'MAIL_PORT' => $_POST['mail_port'] ?? '587',
            'MAIL_USERNAME' => $_POST['mail_username'] ?? '',
            'MAIL_PASSWORD' => $_POST['mail_password'] ?? '',
            'MAIL_ENCRYPTION' => $_POST['mail_encryption'] ?? 'tls',
            'MAIL_FROM_ADDRESS' => $_POST['mail_from_address'] ?? '',
            'MAIL_FROM_NAME' => $_POST['mail_from_name'] ?? '',
            'ADMIN_EMAIL' => $_POST['admin_email'] ?? ''
        ];
        
        // Save to .env file or database
        $this->saveSettings($settings);
        
        $_SESSION['success'] = "Email settings saved successfully!";
        header('Location: ' . BASE_URL . '/admin/settings/email');
        exit;
    }
    
    /**
     * Test email configuration
     */
    public function test()
    {
        @session_start();
        
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['admin_id'])) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }
        
        $testEmail = $_POST['test_email'] ?? $_SESSION['admin_email'] ?? '';
        
        if (empty($testEmail)) {
            echo json_encode(['success' => false, 'error' => 'No test email provided']);
            exit;
        }
        
        try {
            $emailService = new EmailService();
            
            $subject = "Test Email - APS Dream Home";
            $body = "<h2>Test Email</h2><p>This is a test email from APS Dream Home.</p><p>If you received this, your email configuration is working!</p>";
            
            $result = $emailService->send($testEmail, $subject, $body);
            
            echo json_encode(['success' => $result]);
            
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
    
    /**
     * Get email statistics
     */
    private function getEmailStats()
    {
        return [
            'total_sent' => $this->db->fetchOne("SELECT COUNT(*) as count FROM notifications_unified WHERE status = 'sent'")['count'] ?? 0,
            'total_failed' => $this->db->fetchOne("SELECT COUNT(*) as count FROM notifications_unified WHERE status = 'failed'")['count'] ?? 0,
            'today_sent' => $this->db->fetchOne("SELECT COUNT(*) as count FROM notifications_unified WHERE status = 'sent' AND DATE(created_at) = CURDATE()")['count'] ?? 0,
            'today_failed' => $this->db->fetchOne("SELECT COUNT(*) as count FROM notifications_unified WHERE status = 'failed' AND DATE(created_at) = CURDATE()")['count'] ?? 0
        ];
    }
    
    /**
     * Get recent email logs
     */
    private function getRecentLogs($limit = 50)
    {
        return $this->db->fetchAll(
            "SELECT * FROM notifications_unified ORDER BY created_at DESC LIMIT ?",
            [$limit]
        );
    }
    
    /**
     * Get settings from database or env
     */
    private function getSettings()
    {
        return [
            'MAIL_MAILER' => $_ENV['MAIL_MAILER'] ?? 'smtp',
            'MAIL_HOST' => $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com',
            'MAIL_PORT' => $_ENV['MAIL_PORT'] ?? '587',
            'MAIL_USERNAME' => $_ENV['MAIL_USERNAME'] ?? '',
            'MAIL_PASSWORD' => $_ENV['MAIL_PASSWORD'] ?? '',
            'MAIL_ENCRYPTION' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
            'MAIL_FROM_ADDRESS' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@apsdreamhome.com',
            'MAIL_FROM_NAME' => $_ENV['MAIL_FROM_NAME'] ?? 'APS Dream Home',
            'ADMIN_EMAIL' => $_ENV['ADMIN_EMAIL'] ?? ''
        ];
    }
    
    /**
     * Save settings
     */
    private function saveSettings($settings)
    {
        // For now, save to a PHP config file
        $configContent = "<?php\n// Auto-generated email configuration\nreturn [\n";
        foreach ($settings as $key => $value) {
            $configContent .= "    '{$key}' => " . var_export($value, true) . ",\n";
        }
        $configContent .= "];\n";
        
        $configFile = __DIR__ . '/../../../../config/email.php';
        file_put_contents($configFile, $configContent);
    }
}
