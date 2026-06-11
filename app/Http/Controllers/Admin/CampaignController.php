<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\CampaignService;
use Exception;

class CampaignController extends AdminController
{
    private $campaignService;

    public function __construct()
    {
        parent::__construct();
        $this->campaignService = new CampaignService();
    }

    /**
     * Display campaigns management page
     */
    public function index()
    {
        $campaigns = $this->campaignService->getActiveCampaigns();

        $this->data['campaigns'] = $campaigns;
        $this->data['page_title'] = 'Campaign Management - APS Dream Home';

        $this->render('admin/campaigns/index');
    }

    /**
     * Display create campaign form
     */
    public function create()
    {
        $this->middleware('admin.auth');

        $this->data['page_title'] = 'Create Campaign - APS Dream Home';
        $this->data['campaign_types'] = ['general', 'offer', 'promotion', 'announcement'];
        $this->data['target_audiences'] = ['all', 'users', 'users', 'users', 'admin'];

        $this->render('admin/campaigns/create');
    }

    /**
     * Store new campaign
     */
    public function store()
    {
        $this->middleware('admin.auth');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/campaigns');
        }

        try {
            $campaignData = [
                'name' => $_POST['name'] ?? '',
                'description' => $_POST['description'] ?? '',
                'type' => $_POST['type'] ?? 'general',
                'target_audience' => $_POST['target_audience'] ?? 'all',
                'start_date' => $_POST['start_date'] ?? date('Y-m-d'),
                'end_date' => $_POST['end_date'] ?? null,
                'budget' => $_POST['budget'] ?? 0,
                'expected_revenue' => $_POST['expected_revenue'] ?? 0,
                'status' => 'planned',
                'created_by' => $_SESSION['admin_id'] ?? 1
            ];

            // Validate required fields
            if (empty($campaignData['name'])) {
                $this->data['error'] = 'Campaign name is required';
                return $this->create();
            }

            $campaignId = $this->campaignService->createCampaign($campaignData);

            if ($campaignId) {
                $this->data['success'] = 'Campaign created successfully!';
                return $this->index();
            } else {
                $this->data['error'] = 'Failed to create campaign';
                return $this->create();
            }
        } catch (Exception $e) {
            error_log("Error creating campaign: " . $e->getMessage());
            $this->data['error'] = 'An error occurred while creating the campaign';
            return $this->create();
        }
    }

    /**
     * Display edit campaign form
     */
    public function edit($campaignId)
    {
        $this->middleware('admin.auth');

        // Get campaign details
        $campaign = $this->getCampaignById($campaignId);

        if (!$campaign) {
            $this->data['error'] = 'Campaign not found';
            return $this->index();
        }

        $this->data['campaign'] = $campaign;
        $this->data['page_title'] = 'Edit Campaign - APS Dream Home';
        $this->data['campaign_types'] = ['general', 'offer', 'promotion', 'announcement'];
        $this->data['target_audiences'] = ['all', 'users', 'users', 'users', 'admin'];

        $this->render('admin/campaigns/edit');
    }

    /**
     * Update campaign
     */
    public function update($campaignId)
    {
        $this->middleware('admin.auth');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/campaigns');
        }

        try {
            $campaignData = [
                'name' => $_POST['name'] ?? '',
                'description' => $_POST['description'] ?? '',
                'type' => $_POST['type'] ?? 'general',
                'target_audience' => $_POST['target_audience'] ?? 'all',
                'start_date' => $_POST['start_date'] ?? date('Y-m-d'),
                'end_date' => $_POST['end_date'] ?? null,
                'budget' => $_POST['budget'] ?? 0,
                'expected_revenue' => $_POST['expected_revenue'] ?? 0,
                'status' => $_POST['status'] ?? 'planned'
            ];

            // Validate required fields
            if (empty($campaignData['name'])) {
                $this->data['error'] = 'Campaign name is required';
                return $this->edit($campaignId);
            }

            $result = $this->updateCampaign($campaignId, $campaignData);

            if ($result) {
                $this->data['success'] = 'Campaign updated successfully!';
                return $this->index();
            } else {
                $this->data['error'] = 'Failed to update campaign';
                return $this->edit($campaignId);
            }
        } catch (Exception $e) {
            error_log("Error updating campaign: " . $e->getMessage());
            $this->data['error'] = 'An error occurred while updating the campaign';
            return $this->edit($campaignId);
        }
    }

    /**
     * Delete campaign
     */
    public function delete($campaignId)
    {
        $this->middleware('admin.auth');

        try {
            $result = $this->deleteCampaign($campaignId);

            if ($result) {
                $this->data['success'] = 'Campaign deleted successfully!';
            } else {
                $this->data['error'] = 'Failed to delete campaign';
            }
        } catch (Exception $e) {
            error_log("Error deleting campaign: " . $e->getMessage());
            $this->data['error'] = 'An error occurred while deleting the campaign';
        }

        return $this->index();
    }

    /**
     * Display campaign analytics
     */
    public function analytics($campaignId)
    {
        $this->middleware('admin.auth');

        $campaign = $this->getCampaignById($campaignId);

        if (!$campaign) {
            $this->data['error'] = 'Campaign not found';
            return $this->index();
        }

        $this->data['campaign'] = $campaign;
        $this->data['page_title'] = 'Campaign Analytics - APS Dream Home';

        $this->render('admin/campaigns/analytics');
    }

    /**
     * Get campaign by ID
     */
    private function getCampaignById($campaignId)
    {
        try {
            $query = "SELECT * FROM campaigns WHERE campaign_id = ?";
            return $this->db->fetch($query, [$campaignId]);
        } catch (Exception $e) {
            error_log("Error getting campaign: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update campaign in database
     */
    private function updateCampaign($campaignId, $data)
    {
        try {
            $setClause = [];
            $params = [];

            foreach ($data as $key => $value) {
                if ($key !== 'campaign_id') {
                    $setClause[] = "$key = ?";
                    $params[] = $value;
                }
            }

            $params[] = $campaignId;

            $query = "UPDATE campaigns SET " . implode(', ', $setClause) . " WHERE campaign_id = ?";

            $this->db->execute($query, $params);
            return true;
        } catch (Exception $e) {
            error_log("Error updating campaign: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete campaign from database
     */
    private function deleteCampaign($campaignId)
    {
        try {
            try {
                // Delete campaign members first
                $this->db->execute("DELETE FROM campaign_members WHERE campaign_id = ?", [$campaignId]);
            } catch (\Throwable $e) {
                // Gracefully handle dropped table ref
            }

            // Delete campaign
            $this->db->execute("DELETE FROM campaigns WHERE campaign_id = ?", [$campaignId]);

            return true;
        } catch (Exception $e) {
            error_log("Error deleting campaign: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Launch campaign
     */
    public function launch($campaignId)
    {
        $this->middleware('admin.auth');

        try {
            $result = $this->updateCampaign($campaignId, ['status' => 'active']);

            if ($result) {
                // Create notifications for target audience
                $campaign = $this->getCampaignById($campaignId);
                if ($campaign) {
                    $this->createCampaignNotifications($campaign);
                }

                $this->data['success'] = 'Campaign launched successfully!';
            } else {
                $this->data['error'] = 'Failed to launch campaign';
            }
        } catch (Exception $e) {
            error_log("Error launching campaign: " . $e->getMessage());
            $this->data['error'] = 'An error occurred while launching the campaign';
        }

        return $this->index();
    }

    /**
     * Create notifications for campaign
     */
    private function createCampaignNotifications($campaign)
    {
        try {
            // Get target users based on campaign audience
            $targetUsers = $this->getTargetUsers($campaign['target_audience']);

            foreach ($targetUsers as $user) {
                $this->campaignService->createNotification(
                    $user['id'],
                    $campaign['name'],
                    $campaign['description'],
                    'campaign',
                    $campaign['campaign_id']
                );
            }
        } catch (Exception $e) {
            error_log("Error creating campaign notifications: " . $e->getMessage());
        }
    }

    /**
     * Get target users based on audience
     */
    private function getTargetUsers($targetAudience)
    {
        try {
            $query = "SELECT id FROM users";
            $params = [];

            switch ($targetAudience) {
                case 'users':
                    $query .= " WHERE role = 'customer'";
                    break;
                case 'users':
                    $query .= " WHERE role = 'associate'";
                    break;
                case 'users':
                    $query .= " WHERE role = 'employee'";
                    break;
                case 'admin':
                    $query .= " WHERE role = 'admin'";
                    break;
                case 'all':
                default:
                    // No filter for all users
                    break;
            }

            return $this->db->fetchAll($query, $params);
        } catch (Exception $e) {
            error_log("Error getting target users: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Email templates management
     */
    public function emailTemplates()
    {
        $this->data['page_title'] = 'Email Templates';
        $this->data['templates'] = [];
        $this->render('admin/campaigns/email-templates');
    }

    /**
     * Email template editor
     */
    public function templateEditor()
    {
        $this->middleware('admin.auth');
        $this->data['page_title'] = 'Email Template Editor';

        try {
            $stmt = $this->db->query("SELECT * FROM email_templates ORDER BY template_name ASC");
            $templates = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $this->data['templates'] = $templates ?: [];
        } catch (\Exception $e) {
            error_log("Template editor error: " . $e->getMessage());
            $this->data['templates'] = [];
        }

        $this->render('admin/emails/template-editor');
    }

    /**
     * Save email template
     */
    public function saveTemplate()
    {
        $this->middleware('admin.auth');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/email-templates/editor');
        }

        $templateCode = trim($_POST['template_code'] ?? '');
        $templateName = trim($_POST['template_name'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $bodyHtml = $_POST['body_html'] ?? '';

        if (empty($templateCode) || empty($subject)) {
            $this->data['error'] = 'Template code and subject are required.';
            return $this->templateEditor();
        }

        try {
            // Check if template exists
            $check = $this->db->prepare("SELECT id FROM email_templates WHERE template_code = ?");
            $check->execute([$templateCode]);
            $existing = $check->fetch(\PDO::FETCH_ASSOC);

            if ($existing) {
                $stmt = $this->db->prepare("UPDATE email_templates SET template_name = ?, subject = ?, body_html = ?, updated_at = NOW() WHERE template_code = ?");
                $stmt->execute([$templateName, $subject, $bodyHtml, $templateCode]);
                $this->data['success'] = 'Template updated successfully!';
            } else {
                $stmt = $this->db->prepare("INSERT INTO email_templates (template_code, template_name, subject, body_html, html_content, template_type, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 'general', NOW(), NOW())");
                $stmt->execute([$templateCode, $templateName, $subject, $bodyHtml, $bodyHtml]);
                $this->data['success'] = 'Template created successfully!';
            }
        } catch (\Exception $e) {
            error_log("Save template error: " . $e->getMessage());
            $this->data['error'] = 'Failed to save template.';
        }

        $this->templateEditor();
    }

    /**
     * Email logs viewer
     */
    public function logs()
    {
        $this->middleware('admin.auth');
        $this->data['page_title'] = 'Email Logs';

        $statusFilter = $_GET['status'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 50;
        $offset = ($page - 1) * $perPage;

        try {
            $where = '';
            $params = [];
            if ($statusFilter && in_array($statusFilter, ['sent', 'failed', 'pending', 'processing', 'cancelled'])) {
                $where = "WHERE status = ?";
                $params[] = $statusFilter;
            }

            $countStmt = $this->db->prepare("SELECT COUNT(*) as total FROM email_queue $where");
            $countStmt->execute($params);
            $total = (int)$countStmt->fetch(\PDO::FETCH_ASSOC)['total'];

            $stmt = $this->db->prepare("SELECT * FROM email_queue $where ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
            $stmt->execute($params);
            $logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $this->data['logs'] = $logs ?: [];
            $this->data['total'] = $total;
            $this->data['page'] = $page;
            $this->data['perPage'] = $perPage;
            $this->data['totalPages'] = ceil($total / $perPage);
            $this->data['statusFilter'] = $statusFilter;
        } catch (\Exception $e) {
            error_log("Email logs error: " . $e->getMessage());
            $this->data['logs'] = [];
            $this->data['total'] = 0;
            $this->data['page'] = 1;
            $this->data['perPage'] = 50;
            $this->data['totalPages'] = 1;
            $this->data['statusFilter'] = '';
        }

        $this->render('admin/emails/logs');
    }

    /**
     * SMS campaigns management
     */
    public function smsCampaigns()
    {
        $this->data['page_title'] = 'SMS Campaigns';
        $this->data['campaigns'] = [];
        $this->render('admin/campaigns/sms-campaigns');
    }

    /**
     * WhatsApp broadcast management
     */
    public function whatsappBroadcast()
    {
        $this->requireAdmin();

        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();

            $templates = $db->query("SELECT template_name, language FROM whatsapp_templates WHERE status = 'approved' ORDER BY template_name")->fetchAll(\PDO::FETCH_ASSOC);

            $stats['users'] = $db->query("SELECT COUNT(*) as cnt FROM users WHERE phone IS NOT NULL AND phone != ''")->fetch(\PDO::FETCH_ASSOC)['cnt'] ?? 0;
            $stats['leads'] = $db->query("SELECT COUNT(*) as cnt FROM leads WHERE phone IS NOT NULL AND phone != ''")->fetch(\PDO::FETCH_ASSOC)['cnt'] ?? 0;
            $stats['users'] = $db->query("SELECT COUNT(*) as cnt FROM mlm_associates ma JOIN users u ON ma.user_id = u.id WHERE u.phone IS NOT NULL AND u.phone != ''")->fetch(\PDO::FETCH_ASSOC)['cnt'] ?? 0;

            if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->validateCsrfOrFail();
                $audience = $_POST['audience'] ?? '';
                $templateName = $_POST['template_name'] ?? '';
                $message = $_POST['message'] ?? '';
                $customPhones = $_POST['custom_phones'] ?? '';

                $phones = [];
                if ($audience === 'custom') {
                    $phones = array_filter(array_map('trim', explode("\n", $customPhones)));
                } elseif ($audience === 'all_customers') {
                    $rows = $db->query("SELECT phone FROM users WHERE phone IS NOT NULL AND phone != ''")->fetchAll(\PDO::FETCH_ASSOC);
                    $phones = array_column($rows, 'phone');
                } elseif ($audience === 'all_leads') {
                    $rows = $db->query("SELECT phone FROM leads WHERE phone IS NOT NULL AND phone != ''")->fetchAll(\PDO::FETCH_ASSOC);
                    $phones = array_column($rows, 'phone');
                } elseif ($audience === 'all_associates') {
                    $rows = $db->query("SELECT u.phone FROM mlm_associates ma JOIN users u ON ma.user_id = u.id WHERE u.phone IS NOT NULL AND u.phone != ''")->fetchAll(\PDO::FETCH_ASSOC);
                    $phones = array_column($rows, 'phone');
                } elseif ($audience === 'recent_inquiries') {
                    $rows = $db->query("SELECT DISTINCT phone FROM inquiries WHERE phone IS NOT NULL AND phone != '' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)")->fetchAll(\PDO::FETCH_ASSOC);
                    $phones = array_column($rows, 'phone');
                }

                $sent = 0; $failed = 0;
                foreach ($phones as $phone) {
                    try {
                        $wa = new \App\Services\Communication\WhatsAppService();
                        if (!empty($templateName)) {
                            $wa->sendTemplate($phone, $templateName, ['message' => $message]);
                        } else {
                            $wa->sendMessage($phone, $message);
                        }
                        $sent++;
                    } catch (\Exception $e) {
                        $failed++;
                    }
                }

                $message_text = "Broadcast sent: {$sent} successful, {$failed} failed.";
                $message_type = $failed > 0 ? 'warning' : 'success';
            }
        } catch (\Exception $e) {
            $templates = $templates ?? [];
            $stats = $stats ?? ['users' => 0, 'leads' => 0, 'users' => 0];
            $message_text = $message_text ?? ($_SERVER['REQUEST_METHOD'] === 'POST' ? 'Broadcast failed: ' . $e->getMessage() : '');
            $message_type = $message_type ?? 'danger';
        }

        $this->data['page_title'] = 'WhatsApp Broadcast';
        $this->data['templates'] = $templates ?? [];
        $this->data['stats'] = $stats ?? [];
        $this->data['message'] = $message_text ?? '';
        $this->data['message_type'] = $message_type ?? '';

        $this->render('admin/whatsapp/broadcast');
    }
}
