<?php

namespace App\Http\Controllers\Admin;

use App\Services\ServiceConfigService;

/**
 * Admin controller for managing centralized service configurations.
 *
 * Routes (registered in routes/web.php):
 *   GET  /admin/service-configs             -> index()
 *   POST /admin/service-configs/update      -> update()
 *   POST /admin/service-configs/test/{svc}  -> testConnection()
 *   POST /admin/service-configs/reset/{svc} -> resetService()
 */
class ServiceConfigController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->layout = 'layouts/admin';
        $this->data = [];
    }

    /**
     * Main settings page — all services rendered as tabs.
     */
    public function index()
    {
        $this->requireAdmin();

        $svc = ServiceConfigService::getInstance();
        $groups = $svc->getAllGroups();

        return $this->render('admin/service-configs/index', [
            'page_title'   => 'Service Configuration',
            'page_heading' => 'Service Configuration',
            'groups'       => $groups,
            'services'     => $svc->getAll(),
            'saved'        => $_GET['saved'] ?? null,
            'error'        => $_GET['error'] ?? null,
        ]);
    }

    /**
     * Save configs submitted from the settings page.
     */
    public function update()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/admin/service-configs');
            return;
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $this->redirect(BASE_URL . '/admin/service-configs?error=Invalid+CSRF+token');
            return;
        }

        $configs = $_POST['configs'] ?? [];
        if (!is_array($configs)) {
            $this->redirect(BASE_URL . '/admin/service-configs?error=Invalid+request');
            return;
        }

        $svc = ServiceConfigService::getInstance();
        $updated = 0;

        foreach ($configs as $service => $pairs) {
            if (!is_array($pairs)) {
                continue;
            }
            foreach ($pairs as $key => $value) {
                // Boolean checkboxes send nothing when unchecked
                $row = $svc->get($service, $key);
                $svc->set($service, $key, $value);
                $updated++;
            }
        }

        $this->redirect(BASE_URL . '/admin/service-configs?saved=' . urlencode("Saved {$updated} configuration(s)"));
    }

    /**
     * Test a service's API connection (stub — extend per service).
     */
    public function testConnection(string $service)
    {
        $this->requireAdmin();

        $svc = ServiceConfigService::getInstance();
        $config = $svc->getApiConfig($service);

        // Placeholder — real implementations check actual APIs
        $result = [
            'service'  => $service,
            'test_mode'=> $svc->isTestMode($service),
            'has_keys' => count($config) > 0,
            'status'   => 'unknown',
            'message'  => 'Connection test not implemented for this service yet.',
        ];

        if (empty($config)) {
            $result['status'] = 'error';
            $result['message'] = 'No configuration found for this service.';
        } else {
            $result['status'] = 'ok';
            $result['message'] = 'Configuration present. API connection test not yet wired.';
        }

        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    /**
     * Reset a service's configs to seed defaults.
     */
    public function resetService(string $service)
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/admin/service-configs');
            return;
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $this->redirect(BASE_URL . '/admin/service-configs?error=Invalid+CSRF+token');
            return;
        }

        $pdo = $this->getDb();
        $stmt = $pdo->prepare("DELETE FROM `service_configs` WHERE `service_name` = ?");
        $stmt->execute([$service]);

        // Re-seed defaults for this service from the full seed list
        $defaults = $this->getSeedDefaults();
        if (isset($defaults[$service])) {
            $ins = $pdo->prepare(
                "INSERT INTO `service_configs`
                    (`service_name`,`config_key`,`config_value`,`config_type`,`description`,`is_secret`,`group_name`,`sort_order`)
                 VALUES (?,?,?,?,?,?,?,?)"
            );
            foreach ($defaults[$service] as $row) {
                $ins->execute([$service, ...$row]);
            }
        }

        $this->redirect(BASE_URL . '/admin/service-configs?saved=' . urlencode("Reset {$service} to defaults"));
    }

    /**
     * Seed default values per service (mirrors migration script).
     *
     * @return array<string, array<int, array>>
     */
    private function getSeedDefaults(): array
    {
        return [
            'leegality' => [
                ['api_key',    '',    'password', 'Leegality API key',              1, 'integrations', 10],
                ['test_mode',  '1',   'boolean',  'Use Leegality sandbox',          0, 'integrations', 20],
            ],
            'gstn' => [
                ['gstin',     '',    'text',     'GST Identification Number',      0, 'tax', 10],
                ['username',  '',    'text',     'GSTN portal username',           0, 'tax', 20],
                ['password',  '',    'password', 'GSTN portal password',           1, 'tax', 30],
                ['api_key',   '',    'password', 'GSTN API key',                   1, 'tax', 40],
                ['test_mode', '1',   'boolean',  'Use GSTN sandbox',               0, 'tax', 50],
            ],
            'tin' => [
                ['username',  '',    'text',     'TIN portal username',            0, 'tax', 10],
                ['password',  '',    'password', 'TIN portal password',            1, 'tax', 20],
                ['api_key',   '',    'password', 'TIN API key',                    1, 'tax', 30],
                ['test_mode', '1',   'boolean',  'Use TIN sandbox',                0, 'tax', 40],
            ],
            'razorpay' => [
                ['key_id',        '',    'password', 'Razorpay Key ID',              1, 'payments', 10],
                ['key_secret',    '',    'password', 'Razorpay Key Secret',          1, 'payments', 20],
                ['webhook_secret','',    'password', 'Razorpay Webhook Secret',      1, 'payments', 30],
                ['test_mode',     '1',   'boolean',  'Use Razorpay test mode',       0, 'payments', 40],
            ],
            'twilio' => [
                ['account_sid',     '',    'password', 'Twilio Account SID',          1, 'communications', 10],
                ['auth_token',      '',    'password', 'Twilio Auth Token',           1, 'communications', 20],
                ['from_number',     '',    'text',     'Twilio phone (E.164)',        0, 'communications', 30],
                ['whatsapp_number', '',    'text',     'Twilio WhatsApp (E.164)',     0, 'communications', 40],
                ['test_mode',       '1',   'boolean',  'Use Twilio sandbox',          0, 'communications', 50],
            ],
            'aws_s3' => [
                ['access_key',     '',    'password', 'AWS Access Key ID',            1, 'storage', 10],
                ['secret_key',     '',    'password', 'AWS Secret Access Key',        1, 'storage', 20],
                ['region',         'ap-south-1', 'text', 'AWS region',                0, 'storage', 30],
                ['bucket',         '',    'text',     'S3 bucket name',               0, 'storage', 40],
                ['use_path_style', '0',   'boolean',  'Path-style (MinIO/Spaces)',    0, 'storage', 50],
            ],
            'exchange_rate' => [
                ['primary_api', 'https://open.er-api.com/v6/latest/INR', 'text', 'Primary API URL', 0, 'integrations', 10],
                ['fallback_api','',    'text',     'Fallback API URL',                   0, 'integrations', 20],
                ['cache_ttl',   '3600','number',   'Cache TTL in seconds',               0, 'integrations', 30],
                ['test_mode',   '1',   'boolean',  'Use test mode',                      0, 'integrations', 40],
            ],
            'smtp' => [
                ['host',        '',    'text',     'SMTP hostname',                     0, 'communications', 10],
                ['port',        '587', 'number',   'SMTP port',                         0, 'communications', 20],
                ['username',    '',    'text',     'SMTP username',                     0, 'communications', 30],
                ['password',    '',    'password', 'SMTP password',                     1, 'communications', 40],
                ['from_email',  '',    'text',     'From email address',                0, 'communications', 50],
                ['from_name',   '',    'text',     'From name',                         0, 'communications', 60],
                ['encryption',  'tls', 'text',     'tls / ssl / none',                  0, 'communications', 70],
            ],
            'general' => [
                ['app_name',       'APS Dream Home', 'text',    'Application name',           0, 'general', 10],
                ['app_url',        '',                'text',    'Application base URL',       0, 'general', 20],
                ['support_email',  '',                'text',    'Support email',              0, 'general', 30],
                ['support_phone',  '',                'text',    'Support phone',              0, 'general', 40],
                ['company_name',   '',                'text',    'Registered company name',    0, 'general', 50],
                ['company_cin',    '',                'text',    'Corporate Identity Number',  0, 'general', 60],
                ['company_gstin',  '',                'text',    'Company GSTIN',              0, 'general', 70],
            ],
        ];
    }

}
