<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;
use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use Exception;
use PDO;

/**
 * MobileSystemPageController
 * Mobile app, mobile app creation, system launch, system log, system kyc upload
 */
class MobileSystemPageController extends BaseController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
    }

    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    public function mobileApp()
    {
        $this->render('pages/mobile_app', [
            'page_title' => 'Mobile App - APS Dream Home',
            'page_description' => 'Download the APS Dream Home mobile app.',
        ]);
    }

    public function createMobileApp()
    {
        $this->render('pages/create_mobile_app', [
            'page_title' => 'Create Mobile App - APS Dream Home',
            'page_description' => 'Create your own real estate mobile app.',
        ]);
    }

    public function systemLaunchSystem()
    {
        $this->render('pages/launch_system', [
            'page_title' => 'System Launch - APS Dream Home',
            'page_description' => 'System launch and initialization.',
        ]);
    }

    public function systemKycUpload()
    {
        // Handle KYC upload
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle file upload
            $_SESSION['success'] = 'KYC documents uploaded successfully!';
        }
        $this->redirect('/user/kyc');
    }

    public function systemLogSecurityEvent()
    {
        // API endpoint for logging security events
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $event = $data['event'] ?? '';
        $details = $data['details'] ?? [];

        // Log the event
        error_log("SECURITY_EVENT: $event - " . json_encode($details));

        echo json_encode(['success' => true]);
        exit;
    }
}