<?php

namespace App\Http\Controllers\Admin;

use App\Services\DripCampaignService;
use App\Services\AuditService;

class DripCampaignController extends AdminController
{
    private $service;
    private $audit;

    public function __construct($db = null, $auth = null, array $config = [])
    {
        parent::__construct($db, $auth, $config);
        try { $this->service = new DripCampaignService($this->db); } catch (\Throwable $e) { $this->service = null; }
        try { $this->audit = new AuditService($this->db); } catch (\Throwable $e) { $this->audit = null; }
    }

    protected function getUserId(): ?int
    {
        return (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0) ?: null;
    }

    protected function getUserRole(): ?string
    {
        return $_SESSION['admin_role'] ?? $_SESSION['role'] ?? null;
    }

    public function index()
    {
        $stats = $this->service ? $this->service->getStats() : [];
        $campaigns = $this->service ? $this->service->getAllCampaigns(20) : [];
        $result = $this->service ? $this->service->processQueue(50) : [];
        return $this->render('admin.drip_campaigns.index', [
            'page_title' => 'Drip Campaigns',
            'page_heading' => 'Lead Nurturing Drip Campaigns',
            'stats' => $stats,
            'campaigns' => $campaigns,
            'last_process' => $result
        ]);
    }

    public function show($id = 0)
    {
        $id = $id ?: (int)($_GET['id'] ?? 0);
        if (!$this->service || !$id) {
            return $this->redirect(BASE_URL . '/admin/drip-campaigns');
        }
        $campaign = $this->service->getCampaignById($id);
        if (!$campaign) {
            $this->setFlash('error', 'Campaign not found');
            return $this->redirect(BASE_URL . '/admin/drip-campaigns');
        }
        $emails = $this->service->getEmails($id);
        $enrollments = $this->service->getEnrollments($id, 30);
        return $this->render('admin.drip_campaigns.show', [
            'page_title' => $campaign['name'],
            'page_heading' => $campaign['name'],
            'campaign' => $campaign,
            'emails' => $emails,
            'enrollments' => $enrollments
        ]);
    }

    public function create()
    {
        return $this->render('admin.drip_campaigns.create', [
            'page_title' => 'Create Drip Campaign',
            'page_heading' => 'Create Drip Campaign'
        ]);
    }

    public function store()
    {
        if (!$this->service) {
            $this->setFlash('error', 'Service unavailable');
            return $this->redirect(BASE_URL . '/admin/drip-campaigns');
        }
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $trigger = $_POST['trigger_event'] ?? 'new_lead';
        $status = $_POST['status'] ?? 'draft';
        if (!$name) {
            $this->setFlash('error', 'Name is required');
            return $this->redirect(BASE_URL . '/admin/drip-campaigns/create');
        }
        $campaignId = $this->service->createCampaign([
            'name' => $name,
            'description' => $description,
            'trigger_event' => $trigger,
            'status' => $status,
            'created_by' => $this->getUserId()
        ]);

        $emails = $_POST['emails'] ?? [];
        $order = 1;
        foreach ($emails as $e) {
            if (!empty($e['subject']) && !empty($e['body'])) {
                $this->service->addEmail($campaignId, [
                    'sequence_order' => $order++,
                    'delay_days' => (int)($e['delay_days'] ?? 0),
                    'delay_hours' => (int)($e['delay_hours'] ?? 0),
                    'subject' => $e['subject'],
                    'body' => $e['body'],
                    'channel' => $e['channel'] ?? 'email'
                ]);
            }
        }
        if ($this->audit) {
            $this->audit->log('drip_campaign.create', $this->getUserId(), $this->getUserRole(), 'drip_campaign', $campaignId, "Created drip campaign: $name");
        }
        $this->setFlash('success', "Campaign #$campaignId created with $order emails");
        return $this->redirect(BASE_URL . '/admin/drip-campaigns/show/' . $campaignId);
    }

    public function process()
    {
        if ($this->service) {
            $result = $this->service->processQueue(200);
            $this->setFlash('success', "Processed {$result['processed']} enrollments, sent {$result['sent']} emails, completed {$result['completed']}");
        }
        return $this->redirect(BASE_URL . '/admin/drip-campaigns');
    }

    public function toggle()
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($this->service && $id) {
            $campaign = $this->service->getCampaignById($id);
            if ($campaign) {
                $newStatus = $campaign['status'] === 'active' ? 'paused' : 'active';
                $this->pdo()->prepare("UPDATE drip_campaigns SET status = ? WHERE id = ?")->execute([$newStatus, $id]);
                $this->setFlash('success', "Campaign $newStatus");
            }
        }
        return $this->redirect(BASE_URL . '/admin/drip-campaigns');
    }

    public function delete()
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($this->service && $id) {
            try {
                $this->pdo()->prepare("DELETE FROM drip_email_log WHERE campaign_id = ?")->execute([$id]);
                $this->pdo()->prepare("DELETE FROM drip_enrollments WHERE campaign_id = ?")->execute([$id]);
                $this->pdo()->prepare("DELETE FROM drip_emails WHERE campaign_id = ?")->execute([$id]);
                $this->pdo()->prepare("DELETE FROM drip_campaigns WHERE id = ?")->execute([$id]);
                $this->setFlash('success', 'Campaign deleted');
            } catch (\Throwable $e) {
                $this->setFlash('error', 'Delete failed: ' . $e->getMessage());
            }
        }
        return $this->redirect(BASE_URL . '/admin/drip-campaigns');
    }

    private function pdo(): \PDO
    {
        $db = $this->db;
        if (is_object($db) && method_exists($db, 'getPdo')) return $db->getPdo();
        return $db;
    }
}
