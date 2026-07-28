<?php

namespace App\Http\Controllers\Admin;

use App\Core\Database\Database;

/**
 * Bulk Outreach Controller — WhatsApp/SMS Campaign Management
 */
class BulkOutreachController extends AdminController
{
    use \App\Traits\TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Campaign list + create form
     * GET /admin/crm/outreach
     */
    public function index()
    {
        $this->requireAdmin();

        try {
            $db = Database::getInstance()->getConnection();
            $campaigns = $db->query("
                SELECT c.*, 
                       (SELECT COUNT(*) FROM campaign_deliveries cd WHERE cd.campaign_id = c.id) as total_sent,
                       (SELECT COUNT(*) FROM campaign_deliveries cd WHERE cd.campaign_id = c.id AND cd.status='delivered') as delivered,
                       (SELECT COUNT(*) FROM campaign_deliveries cd WHERE cd.campaign_id = c.id AND cd.status='replied') as replied,
                       u.name as creator_name
                FROM crm_campaigns c
                LEFT JOIN users u ON c.created_by = u.id
                ORDER BY c.created_at DESC
            ")->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            $leadStats = [
                'total' => (int)$db->query("SELECT COUNT(*) FROM leads WHERE deleted_at IS NULL")->fetchColumn(),
                'with_phone' => (int)$db->query("SELECT COUNT(*) FROM leads WHERE phone IS NOT NULL AND phone != '' AND deleted_at IS NULL")->fetchColumn(),
                'new' => (int)$db->query("SELECT COUNT(*) FROM leads WHERE status='new' AND deleted_at IS NULL")->fetchColumn(),
                'contacted' => (int)$db->query("SELECT COUNT(*) FROM leads WHERE status='contacted' AND deleted_at IS NULL")->fetchColumn(),
            ];
        } catch (\Throwable $e) {
            $campaigns = [];
            $leadStats = ['total' => 0, 'with_phone' => 0, 'new' => 0, 'contacted' => 0];
        }

        return $this->render('admin/crm/outreach', [
            'campaigns' => $campaigns,
            'lead_stats' => $leadStats,
            'page_title' => 'Bulk Outreach',
            'current_page' => 'crm',
        ]);
    }

    /**
     * Create a new campaign
     * POST /admin/crm/outreach/create
     */
    public function createCampaign()
    {
        $this->requireAdmin();

        $name = trim($_POST['name'] ?? '');
        $type = $_POST['campaign_type'] ?? 'whatsapp_broadcast';
        $message = trim($_POST['message'] ?? '');
        $targetStatus = $_POST['target_status'] ?? 'all';
        $targetSource = $_POST['target_source'] ?? 'all';

        if (empty($name) || empty($message)) {
            $_SESSION['error'] = 'Campaign name and message are required.';
            header('Location: ' . BASE_URL . '/admin/crm/outreach');
            exit;
        }

        try {
            $db = Database::getInstance();
            $adminId = $_SESSION['admin_id'] ?? $_SESSION['user_id'];

            // Build target filter
            $targetFilter = ['status' => $targetStatus, 'source' => $targetSource];

            // Count target leads
            $where = "WHERE deleted_at IS NULL AND phone IS NOT NULL AND phone != ''";
            $params = [];
            if ($targetStatus !== 'all') {
                $where .= " AND status = ?";
                $params[] = $targetStatus;
            }
            if ($targetSource !== 'all') {
                $where .= " AND source = ?";
                $params[] = $targetSource;
            }

            $pdo = $db->getConnection();
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM leads $where");
            $stmt->execute($params);
            $targetCount = (int)$stmt->fetchColumn();

            $campaignId = $db->insert('crm_campaigns', [
                'name'           => $name,
                'campaign_type'  => $type,
                'platform'       => $type === 'whatsapp_broadcast' ? 'whatsapp' : 'sms',
                'budget'         => 0,
                'target_audience' => json_encode($targetFilter),
                'status'         => 'draft',
                'total_leads'    => $targetCount,
                'created_by'     => $adminId,
                'created_at'     => date('Y-m-d H:i:s'),
            ]);

            // Store message template
            $db->insert('crm_interactions', [
                'interaction_type' => 'campaign_message',
                'subject'          => $name,
                'message'          => $message,
                'related_id'       => $campaignId,
                'related_type'     => 'campaign',
                'created_by'       => $adminId,
                'created_at'       => date('Y-m-d H:i:s'),
            ]);

            $_SESSION['success'] = "Campaign created. {$targetCount} leads targeted. Ready to send.";
            header('Location: ' . BASE_URL . '/admin/crm/outreach');
            exit;
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Failed to create campaign: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/admin/crm/outreach');
            exit;
        }
    }

    /**
     * Send campaign (queue messages)
     * POST /admin/crm/outreach/{id}/send
     */
    public function sendCampaign($id)
    {
        $this->requireAdmin();

        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();

            // Get campaign
            $campaign = $pdo->prepare("SELECT * FROM crm_campaigns WHERE id = ?");
            $campaign->execute([$id]);
            $campaign = $campaign->fetch(\PDO::FETCH_ASSOC);

            if (!$campaign) {
                $_SESSION['error'] = 'Campaign not found.';
                header('Location: ' . BASE_URL . '/admin/crm/outreach');
                exit;
            }

            if ($campaign['status'] === 'completed') {
                $_SESSION['error'] = 'Campaign already completed.';
                header('Location: ' . BASE_URL . '/admin/crm/outreach');
                exit;
            }

            // Get target leads
            $targetFilter = json_decode($campaign['target_audience'] ?? '{}', true) ?? [];
            $where = "WHERE deleted_at IS NULL AND phone IS NOT NULL AND phone != ''";
            $params = [];
            if (!empty($targetFilter['status']) && $targetFilter['status'] !== 'all') {
                $where .= " AND status = ?";
                $params[] = $targetFilter['status'];
            }
            if (!empty($targetFilter['source']) && $targetFilter['source'] !== 'all') {
                $where .= " AND source = ?";
                $params[] = $targetFilter['source'];
            }

            $leads = $pdo->prepare("SELECT id, name, phone FROM leads $where LIMIT 500");
            $leads->execute($params);
            $leads = $leads->fetchAll(\PDO::FETCH_ASSOC);

            // Get message template
            $msgStmt = $pdo->prepare("SELECT message FROM crm_interactions WHERE related_id = ? AND related_type = 'campaign' LIMIT 1");
            $msgStmt->execute([$id]);
            $msgRow = $msgStmt->fetch(\PDO::FETCH_ASSOC);
            $messageTemplate = $msgRow['message'] ?? '';

            $sent = 0;
            foreach ($leads as $lead) {
                $message = str_replace(['{name}', '{phone}'], [$lead['name'], $lead['phone']], $messageTemplate);

                $db->insert('campaign_deliveries', [
                    'campaign_id'  => $id,
                    'lead_id'      => $lead['id'],
                    'phone'        => $lead['phone'],
                    'message'      => $message,
                    'status'       => 'pending',
                    'created_at'   => date('Y-m-d H:i:s'),
                ]);
                $sent++;
            }

            // Update campaign status
            $pdo->prepare("UPDATE crm_campaigns SET status = 'active', total_leads = ?, updated_at = NOW() WHERE id = ?")->execute([$sent, $id]);

            $_SESSION['success'] = "Campaign queued: {$sent} messages to be sent.";
            header('Location: ' . BASE_URL . '/admin/crm/outreach');
            exit;
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Failed to send campaign: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/admin/crm/outreach');
            exit;
        }
    }

    /**
     * Campaign stats
     * GET /admin/crm/outreach/{id}/stats
     */
    public function campaignStats($id)
    {
        $this->requireAdmin();
        header('Content-Type: application/json');

        try {
            $db = Database::getInstance()->getConnection();
            $campaign = $db->prepare("SELECT * FROM crm_campaigns WHERE id = ?");
            $campaign->execute([$id]);
            $campaign = $campaign->fetch(\PDO::FETCH_ASSOC);

            if (!$campaign) {
                echo json_encode(['error' => 'Not found']);
                exit;
            }

            $deliveries = $db->prepare("SELECT status, COUNT(*) as cnt FROM campaign_deliveries WHERE campaign_id = ? GROUP BY status");
            $deliveries->execute([$id]);
            $deliveryStats = $deliveries->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            echo json_encode([
                'campaign' => $campaign,
                'deliveries' => $deliveryStats,
            ]);
        } catch (\Throwable $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }
}
