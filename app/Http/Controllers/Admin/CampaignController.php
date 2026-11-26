<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\Campaign\CampaignService;
use Exception;

class CampaignController extends AdminController
{
    use \App\Traits\TenantAwareTrait;

    private $campaignService;

    public function __construct()
    {
        parent::__construct();
        $this->campaignService = new \App\Services\Campaign\CampaignService();
    }

    /**
     * Display campaigns management page
     */
    public function index()
    {

        $this->requireAdmin();
        // S772: getActiveCampaigns() filtered status='active', but the live
        // marketing_campaigns enum is draft/scheduled/sending/sent/paused/cancelled
        // — no 'active' member — so this page was always empty. List all.
        $campaigns = $this->campaignService->getCampaigns([], 100);

        $this->data['campaigns'] = $campaigns;
        $this->data['page_title'] = 'Campaign Management - APS Dream Home';

        $this->render('admin/campaigns/index');
    }

    /**
     * Display create campaign form
     */
    public function create()
    {
        $this->requireAdmin();
        $this->middleware('admin.auth');

        $this->data['page_title'] = 'Create Campaign - APS Dream Home';
        // S772: CampaignService::createCampaign() validates type against
        // CHANNELS = email/sms/whatsapp; the old form posted 'general' etc.,
        // which threw InvalidArgumentException on every create.
        $this->data['campaign_types'] = ['email', 'sms', 'whatsapp'];
        $this->data['target_audiences'] = ['all_users', 'customers', 'associates', 'employees', 'admin'];

        $this->render('admin/campaigns/create');
    }

    /**
     * Store new campaign
     */
    public function store()
    {
        $this->requireAdmin();
        $this->middleware('admin.auth');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/campaigns');
        }

        try {
            // S774: CampaignService::createCampaign() reads `audience`, `content`
            // and a valid channel type. The old keys silently defaulted targeting.
            $startDate = (string)($_POST['start_date'] ?? date('Y-m-d'));
            $campaignData = [
                'name' => $_POST['name'] ?? '',
                'description' => $_POST['description'] ?? '',
                'content' => $_POST['content'] ?? $_POST['description'] ?? '',
                'type' => $_POST['type'] ?? 'email',
                'audience' => $_POST['target_audience'] ?? $_POST['audience'] ?? 'all_users',
                'target_audience' => $_POST['target_audience'] ?? 'all', // kept for the view
                'scheduled_at' => preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) ? $startDate . ' 00:00:00' : null,
                'budget' => $_POST['budget'] ?? 0,
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
        } catch (\Throwable $e) {
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
        $this->requireAdmin();
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
        $this->data['target_audiences'] = ['all', 'customers', 'associates', 'employees', 'admin'];

        $this->render('admin/campaigns/edit');
    }

    /**
     * Update campaign
     */
    public function update($campaignId)
    {
        $this->requireAdmin();
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
        } catch (\Throwable $e) {
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
        $this->requireAdmin();
        $this->middleware('admin.auth');

        try {
            $result = $this->deleteCampaign($campaignId);

            if ($result) {
                $this->data['success'] = 'Campaign deleted successfully!';
            } else {
                $this->data['error'] = 'Failed to delete campaign';
            }
        } catch (\Throwable $e) {
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
        $this->requireAdmin();
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
        // S772: CampaignService creates into marketing_campaigns (live table);
        // this controller used to read legacy `campaigns` only, so every live
        // campaign opened as "Campaign not found". Read the live table first and
        // expose the keys the edit/analytics views expect; legacy rows still work.
        try {
            $row = $this->db->fetch("SELECT * FROM marketing_campaigns WHERE id = ?", [$campaignId]);
            if ($row) {
                $row['campaign_id'] = $row['id'];
                $row['start_date'] = !empty($row['scheduled_at']) ? substr((string)$row['scheduled_at'], 0, 10) : null;
                $row['end_date'] = null; // no live end-date column
                $row['expected_revenue'] = 0; // no live column - honest zero
                return $row;
            }
            return $this->db->fetch("SELECT * FROM campaigns WHERE campaign_id = ?", [$campaignId]) ?: null;
        } catch (\Throwable $e) {
            error_log("Error getting campaign: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update campaign in database
     */
    private function updateCampaign($campaignId, $data)
    {
        // S772: only write columns that exist on marketing_campaigns. The edit
        // form still posts legacy keys (start_date/end_date/expected_revenue).
        $map = [
            'name' => 'name',
            'description' => 'description',
            'budget' => 'budget',
            'target_audience' => 'target_audience',
            'subject' => 'subject',
            'content' => 'content',
        ];
        $statusMap = [
            'planned' => 'draft',
            'active' => 'sending',
            'paused' => 'paused',
            'completed' => 'sent',
            'draft' => 'draft',
            'scheduled' => 'scheduled',
            'sending' => 'sending',
            'sent' => 'sent',
            'cancelled' => 'cancelled',
        ];
        $validTypes = ['email', 'sms', 'whatsapp', 'push', 'multi'];
        try {
            $live = $this->db->fetch("SELECT id, type FROM marketing_campaigns WHERE id = ?", [$campaignId]);
            if (!$live) {
                // legacy campaign row: keep the old update path
                $setClause = [];
                $params = [];
                foreach ($data as $key => $value) {
                    if ($key !== 'campaign_id') { $setClause[] = "$key = ?"; $params[] = $value; }
                }
                $params[] = $campaignId;
                $this->db->execute("UPDATE campaigns SET " . implode(', ', $setClause) . " WHERE campaign_id = ?", $params);
                return true;
            }

            $setClause = [];
            $params = [];
            foreach ($map as $input => $column) {
                if (array_key_exists($input, $data)) {
                    $setClause[] = "$column = ?";
                    $params[] = $data[$input];
                }
            }
            if (!empty($data['start_date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$data['start_date'])) {
                $setClause[] = 'scheduled_at = ?';
                $params[] = $data['start_date'] . ' 00:00:00';
            }
            $postedType = (string)($data['type'] ?? '');
            if (in_array($postedType, $validTypes, true)) {
                $setClause[] = 'type = ?';
                $params[] = $postedType;
            } // otherwise keep the live channel - the view's general/offer/... are not channels
            $postedStatus = (string)($data['status'] ?? '');
            if (isset($statusMap[$postedStatus])) {
                $setClause[] = 'status = ?';
                $params[] = $statusMap[$postedStatus];
            }
            if (!$setClause) { return true; } // nothing mappable = no-op, not a 1054
            $params[] = $campaignId;
            $this->db->execute("UPDATE marketing_campaigns SET " . implode(', ', $setClause) . " WHERE id = ?", $params);
            return true;
        } catch (\Throwable $e) {
            error_log("Error updating campaign: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete campaign from database
     */
    private function deleteCampaign($campaignId)
    {
        $tid = (int)$this->tenantId();
        try {
            // S772: campaign_members is a 4-column stub (id/tenant_id/timestamps)
            // with no campaign_id column and 0 rows — the old member DELETE was
            // a phantom-column write that 1054'd on every campaign delete.
            // No schema invented; nothing to clean.

            // S772: live campaigns are in marketing_campaigns (PK id); the
            // legacy table is cleaned too for old rows.
            $this->db->execute("DELETE FROM marketing_campaign_recipients WHERE campaign_id = ? AND tenant_id = ?", [$campaignId, $tid]);
            $this->db->execute("DELETE FROM marketing_campaigns WHERE id = ? AND tenant_id = ?", [$campaignId, $tid]);
            $this->db->execute("DELETE FROM campaigns WHERE campaign_id = ? AND tenant_id = ?", [$campaignId, $tid]);

            return true;
        } catch (\Throwable $e) {
            error_log("Error deleting campaign: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Launch campaign
     */
    public function launch($campaignId)
    {
        $this->requireAdmin();
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
        } catch (\Throwable $e) {
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
        } catch (\Throwable $e) {
            error_log("Error creating campaign notifications: " . $e->getMessage());
        }
    }

    /**
     * Get target users based on audience
     */
    private function getTargetUsers($targetAudience)
    {
        try {
            [$tidSql, $tidParams] = $this->tenantWhere();
            $query = "SELECT id FROM users";
            $params = [];

            switch ($targetAudience) {
                case 'customers':
                case 'users': // legacy value kept for existing campaigns
                    $query .= " WHERE role = 'customer'";
                    break;
                case 'associates':
                    $query .= " WHERE role = 'associate'";
                    break;
                case 'employees':
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

            if (strpos($query, 'WHERE') !== false) {
                $query .= $tidSql;
            } else {
                $query .= " WHERE 1=1{$tidSql}";
            }

            return $this->db->fetchAll($query, array_merge($params, $tidParams));
        } catch (\Throwable $e) {
            error_log("Error getting target users: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Email templates management
     */
    public function emailTemplates()
    {
        $this->requireAdmin();
        $this->data['page_title'] = 'Email Templates';
        try {
            $this->data['templates'] = $this->db->fetchAll(
                "SELECT * FROM email_templates ORDER BY created_at DESC"
            ) ?: [];
        } catch (\Throwable $e) {
            $this->data['templates'] = [];
        }
        $this->render('admin/campaigns/email-templates');
    }

    /**
     * Email template editor
     */
    public function templateEditor()
    {
        $this->requireAdmin();
        $this->middleware('admin.auth');
        $this->data['page_title'] = 'Email Template Editor';

        try {
            $stmt = $this->db->query("SELECT * FROM email_templates ORDER BY template_name ASC");
            $templates = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $this->data['templates'] = $templates ?: [];
        } catch (\Throwable $e) {
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
        $this->requireAdmin();
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

            $tid = (int)$this->tenantId();

            if ($existing) {
                $stmt = $this->db->prepare("UPDATE email_templates SET template_name = ?, subject = ?, body_html = ?, updated_at = NOW() WHERE template_code = ? AND tenant_id = ?");
                $stmt->execute([$templateName, $subject, $bodyHtml, $templateCode, $tid]);
                $this->data['success'] = 'Template updated successfully!';
            } else {
                $stmt = $this->db->prepare("INSERT INTO email_templates (template_code, template_name, subject, body_html, html_content, template_type, tenant_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 'general', ?, NOW(), NOW())");
                $stmt->execute([$templateCode, $templateName, $subject, $bodyHtml, $bodyHtml, $tid]);
                $this->data['success'] = 'Template created successfully!';
            }
        } catch (\Throwable $e) {
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
        $this->requireAdmin();
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
        } catch (\Throwable $e) {
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
        $this->requireAdmin();
        $this->data['page_title'] = 'SMS Campaigns';
        try {
            $this->data['campaigns'] = $this->db->fetchAll(
                "SELECT * FROM campaigns WHERE type = 'sms' OR type IS NULL ORDER BY created_at DESC"
            ) ?: [];
            $this->data['stats'] = [
                'total' => count($this->data['campaigns']),
                'sent' => count(array_filter($this->data['campaigns'], fn($c) => ($c['status'] ?? '') === 'sent')),
                'draft' => count(array_filter($this->data['campaigns'], fn($c) => ($c['status'] ?? '') === 'draft')),
            ];
        } catch (\Throwable $e) {
            $this->data['campaigns'] = [];
            $this->data['stats'] = ['total' => 0, 'sent' => 0, 'draft' => 0];
        }
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

            // S771: live enum is active/inactive/pending_approval/rejected - 'approved' never matched.
            $templates = $db->query("SELECT template_name, language FROM whatsapp_templates WHERE status = 'active' ORDER BY template_name")->fetchAll(\PDO::FETCH_ASSOC);

            [$tidSql, $tidParams] = $this->tenantWhere();
            $stmtUsers = $db->prepare("SELECT COUNT(*) as cnt FROM users WHERE phone IS NOT NULL AND phone != ''{$tidSql}");
            $stmtUsers->execute($tidParams);
            $stats['users'] = $stmtUsers->fetch(\PDO::FETCH_ASSOC)['cnt'] ?? 0;
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
                    $stmtPhones = $db->prepare("SELECT phone FROM users WHERE phone IS NOT NULL AND phone != ''{$tidSql}");
                    $stmtPhones->execute($tidParams);
                    $rows = $stmtPhones->fetchAll(\PDO::FETCH_ASSOC);
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
                        $wa = new \App\Services\Communication\WhatsAppSenderService();
                        // S771: the sender returns ['success'=>false] on failure;
                        // counting every call as sent fabricated the broadcast report.
                        $res = !empty($templateName)
                            ? $wa->sendTemplate($phone, $templateName, ['message' => $message])
                            : $wa->sendMessage($phone, $message);
                        if (!empty($res['success'])) {
                            $sent++;
                        } else {
                            $failed++;
                        }
                    } catch (\Throwable $e) {
                        $failed++;
                    }
                }

                $message_text = "Broadcast sent: {$sent} successful, {$failed} failed.";
                $message_type = $failed > 0 ? 'warning' : 'success';
            }
        } catch (\Throwable $e) {
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
