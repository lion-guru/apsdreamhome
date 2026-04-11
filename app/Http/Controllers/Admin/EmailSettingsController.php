<?php
/**
 * Email Settings Controller
 * Admin can configure SMTP settings here
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use App\Services\Communication\EmailService;

class EmailSettingsController extends BaseController
{
    private $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
    }
    
    /**
     * Show email settings page
     */
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
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
        if (session_status() === PHP_SESSION_NONE) session_start();
        
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
        if (session_status() === PHP_SESSION_NONE) session_start();
        
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
            'total_sent' => $this->db->fetchOne("SELECT COUNT(*) as count FROM email_logs WHERE status = 'sent'")['count'] ?? 0,
            'total_failed' => $this->db->fetchOne("SELECT COUNT(*) as count FROM email_logs WHERE status = 'failed'")['count'] ?? 0,
            'today_sent' => $this->db->fetchOne("SELECT COUNT(*) as count FROM email_logs WHERE status = 'sent' AND DATE(created_at) = CURDATE()")['count'] ?? 0,
            'today_failed' => $this->db->fetchOne("SELECT COUNT(*) as count FROM email_logs WHERE status = 'failed' AND DATE(created_at) = CURDATE()")['count'] ?? 0
        ];
    }
    
    /**
     * Get recent email logs
     */
    private function getRecentLogs($limit = 50)
    {
        return $this->db->fetchAll(
            "SELECT * FROM email_logs ORDER BY created_at DESC LIMIT ?",
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
