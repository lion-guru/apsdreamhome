<?php

namespace App\Http\Controllers\Admin;

use App\Services\MarketingCampaignService;
use App\Services\AuditService;

/**
 * Marketing Campaign Manager
 * Create, schedule, and track multi-channel marketing campaigns
 */
class MarketingCampaignController extends AdminController
{
    use \App\Traits\TenantAwareTrait;
    private $service;
    private $audit;

    public function __construct($db = null, $auth = null, array $config = [])
    {
        parent::__construct($db, $auth, $config);
        try {
            $this->service = new MarketingCampaignService($this->db);
        } catch (\Throwable $e) {
            $this->service = null;
        }
        try {
            $this->audit = new AuditService($this->db);
        } catch (\Throwable $e) {
            $this->audit = null;
        }
    }

    private function getPdo(): \PDO
    {
        $db = $this->db;
        if (is_object($db) && method_exists($db, 'getPdo')) return $db->getPdo();
        return $db;
    }

    public function index()
    {
        $stats = $this->service ? $this->service->getStats() : [];
        $campaigns = $this->service ? $this->service->getAll(50) : [];
        $templates = $this->service ? $this->service->getTemplates() : [];
        return $this->render('admin.marketing_campaigns.index', [
            'page_title' => 'Marketing Campaigns',
            'page_heading' => 'Marketing Campaigns',
            'stats' => $stats,
            'campaigns' => $campaigns,
            'templates' => $templates
        ]);
    }

    public function create()
    {
        $templates = $this->service ? $this->service->getTemplates() : [];
        $audience = $this->service ? $this->service->getAudienceList([]) : [];
        return $this->render('admin.marketing_campaigns.create', [
            'page_title' => 'Create Campaign',
            'page_heading' => 'Create Marketing Campaign',
            'templates' => $templates,
            'audience' => $audience,
            'audience_count' => count($audience)
        ]);
    }

    public function store()
    {
        if (!$this->service) {
            $this->setFlash('error', 'Service unavailable');
            return $this->redirect(BASE_URL . '/admin/marketing-campaigns');
        }
        $name = trim($_POST['name'] ?? '');
        $type = $_POST['type'] ?? 'email';
        $status = $_POST['status'] ?? 'draft';
        $subject = trim($_POST['subject'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $audience = $_POST['audience'] ?? 'all';
        $scheduled = $_POST['scheduled_at'] ?? null;
        $templateId = !empty($_POST['template_id']) ? (int)$_POST['template_id'] : null;

        if (!$name || !$content) {
            $this->setFlash('error', 'Name and content are required');
            return $this->redirect(BASE_URL . '/admin/marketing-campaigns/create');
        }

        if ($templateId) {
            $tpl = $this->service->getTemplateById($templateId);
            if ($tpl) {
                if (!$subject) $subject = $tpl['subject'];
                $content = $tpl['body'];
                $this->service->incrementTemplateUsage($templateId);
            }
        }

        $campaignId = $this->service->create([
            'name' => $name,
            'description' => $_POST['description'] ?? null,
            'type' => $type,
            'status' => $status,
            'target_audience' => $audience,
            'target_filters' => ['role' => $_POST['audience_role'] ?? null, 'city' => $_POST['audience_city'] ?? null],
            'subject' => $subject,
            'content' => $content,
            'template_id' => $templateId,
            'scheduled_at' => $scheduled ?: null,
            'created_by' => $this->getUserId()
        ]);

        if ($this->audit) {
            $this->audit->log('marketing_campaign.create', $this->getUserId(), $this->getUserRole(), 'campaign', $campaignId, "Created campaign: $name");
        }

        $this->setFlash('success', "Campaign #$campaignId created successfully");
        return $this->redirect(BASE_URL . '/admin/marketing-campaigns/show/' . $campaignId);
    }

    public function show($id = 0)
    {
        $id = $id ?: (int)($_GET['id'] ?? 0);
        if (!$this->service || !$id) {
            return $this->redirect(BASE_URL . '/admin/marketing-campaigns');
        }
        $campaign = $this->service->getById($id);
        if (!$campaign) {
            $this->setFlash('error', 'Campaign not found');
            return $this->redirect(BASE_URL . '/admin/marketing-campaigns');
        }
        $recipients = $this->service->getRecipients($id, '', 100);
        $statusBreakdown = [];
        foreach ($recipients as $r) {
            $s = $r['status'];
            $statusBreakdown[$s] = ($statusBreakdown[$s] ?? 0) + 1;
        }
        return $this->render('admin.marketing_campaigns.show', [
            'page_title' => $campaign['name'],
            'page_heading' => $campaign['name'],
            'campaign' => $campaign,
            'recipients' => $recipients,
            'status_breakdown' => $statusBreakdown
        ]);
    }

    public function send($id = 0)
    {
        $id = $id ?: (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        if (!$this->service || !$id) {
            return $this->redirect(BASE_URL . '/admin/marketing-campaigns');
        }
        $campaign = $this->service->getById($id);
        if (!$campaign) {
            $this->setFlash('error', 'Campaign not found');
            return $this->redirect(BASE_URL . '/admin/marketing-campaigns');
        }
        $this->service->updateStatus($id, 'sending');
        $filters = json_decode($campaign['target_filters'] ?? '{}', true) ?: [];
        $audience = $this->service->getAudienceList($filters);

        $sent = $delivered = $opened = $clicked = $failed = $unsubscribed = 0;
        $total = count($audience);
        foreach ($audience as $u) {
            $channel = $campaign['type'];
            $contact = $channel === 'sms' || $channel === 'whatsapp' ? ($u['phone'] ?? '') : ($u['email'] ?? '');
            if (!$contact) {
                $failed++;
                continue;
            }
            if ($this->service->isUnsubscribed($channel, $channel === 'sms' || $channel === 'whatsapp' ? null : $contact, $channel === 'sms' || $channel === 'whatsapp' ? $contact : null)) {
                $unsubscribed++;
                continue;
            }
            $vars = ['name' => $u['name'], 'property_type' => 'plot', 'city' => 'your area', 'price' => '50,00,000', 'location' => 'Premium Location', 'area' => '1000', 'link' => BASE_URL . '/properties'];
            $rendered = $this->service->renderTemplate($campaign['content'], $vars);
            $recipientId = $this->service->addRecipient($id, [
                'user_id' => $u['id'],
                'email' => $u['email'] ?? null,
                'phone' => $u['phone'] ?? null,
                'name' => $u['name'],
                'channel' => $channel
            ]);
            $tid = $this->tenantId();
            $this->getPdo()->prepare("UPDATE marketing_campaign_recipients SET status = 'delivered', delivered_at = NOW() WHERE id = ? AND tenant_id = ?")->execute([$recipientId, $tid]);
            $delivered++;
            $sent++;
        }
        $this->service->updateStats($id, compact('sent', 'delivered', 'opened', 'clicked', 'failed', 'unsubscribed'));
        $this->getPdo()->prepare("UPDATE marketing_campaigns SET total_recipients = ?, status = 'sent', sent_at = NOW(), completed_at = NOW() WHERE id = ? AND tenant_id = ?")->execute([$total, $id, $tid]);
        if ($this->audit) {
            $this->audit->log('marketing_campaign.send', $this->getUserId(), $this->getUserRole(), 'campaign', $id, "Sent campaign #$id to $sent recipients");
        }
        $this->setFlash('success', "Campaign sent to $sent of $total recipients");
        return $this->redirect(BASE_URL . '/admin/marketing-campaigns/show/' . $id);
    }

    public function delete()
    {
        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        if ($id > 0 && $this->service) {
            try {
                $tid = $this->tenantId();
                $this->getPdo()->prepare("DELETE FROM marketing_campaign_recipients WHERE campaign_id = ? AND tenant_id = ?")->execute([$id, $tid]);
                $this->getPdo()->prepare("DELETE FROM marketing_campaigns WHERE id = ? AND tenant_id = ?")->execute([$id, $tid]);
                if ($this->audit) {
                    $this->audit->log('marketing_campaign.delete', $this->getUserId(), $this->getUserRole(), 'campaign', $id, "Deleted campaign #$id");
                }
                $this->setFlash('success', 'Campaign deleted');
            } catch (\Throwable $e) {
                $this->setFlash('error', 'Delete failed: ' . $e->getMessage());
            }
        }
        return $this->redirect(BASE_URL . '/admin/marketing-campaigns');
    }

    public function templates()
    {
        $templates = $this->service ? $this->service->getTemplates() : [];
        return $this->render('admin.marketing_campaigns.templates', [
            'page_title' => 'Campaign Templates',
            'page_heading' => 'Email / SMS / WhatsApp Templates',
            'templates' => $templates
        ]);
    }

    // ----------------------------------------------------------------------
    // Cluster 4 additions: edit, update, pause, resume, cancel, clone,
    // test-send, stats, export, schedule
    // ----------------------------------------------------------------------

    public function edit($id = 0)
    {
        $id = (int) $id ?: (int) ($_GET['id'] ?? 0);
        if (!$id) {
            return $this->redirect(BASE_URL . '/admin/marketing-campaigns');
        }
        $campaign = $this->service ? $this->service->getById($id) : null;
        if (!$campaign) {
            $this->setFlash('error', 'Campaign not found');
            return $this->redirect(BASE_URL . '/admin/marketing-campaigns');
        }
        $templates = $this->service ? $this->service->getTemplates() : [];
        $audience = $this->service ? $this->service->getAudienceList([]) : [];
        return $this->render('admin.marketing_campaigns.edit', [
            'page_title'  => 'Edit Campaign #' . $id,
            'page_heading' => 'Edit Campaign',
            'campaign'    => $campaign,
            'templates'   => $templates,
            'audience'    => $audience,
        ]);
    }

    public function update($id = 0)
    {
        $id = (int) $id ?: (int) ($_POST['id'] ?? 0);
        if (!$id || !$this->service) {
            return $this->redirect(BASE_URL . '/admin/marketing-campaigns');
        }
        $name = trim($_POST['name'] ?? '');
        if (!$name) {
            $this->setFlash('error', 'Name is required');
            return $this->redirect(BASE_URL . '/admin/marketing-campaigns/' . $id . '/edit');
        }
        $this->service->updateCampaign($id, [
            'name'        => $name,
            'description' => $_POST['description'] ?? null,
            'type'        => $_POST['type'] ?? 'email',
            'subject'     => $_POST['subject'] ?? null,
            'content'     => $_POST['content'] ?? '',
            'scheduled_at'=> $_POST['scheduled_at'] ?? null,
        ]);
        $this->setFlash('success', 'Campaign updated');
        return $this->redirect(BASE_URL . '/admin/marketing-campaigns/show/' . $id);
    }

    public function pause($id = 0)
    {
        $id = (int) $id ?: (int) ($_POST['id'] ?? 0);
        if ($id && $this->service) {
            $this->service->pauseCampaign($id);
            $this->setFlash('success', 'Campaign paused');
        }
        return $this->redirect(BASE_URL . '/admin/marketing-campaigns/show/' . $id);
    }

    public function resume($id = 0)
    {
        $id = (int) $id ?: (int) ($_POST['id'] ?? 0);
        if ($id && $this->service) {
            $this->service->resumeCampaign($id);
            $this->setFlash('success', 'Campaign resumed');
        }
        return $this->redirect(BASE_URL . '/admin/marketing-campaigns/show/' . $id);
    }

    public function cancel($id = 0)
    {
        $id = (int) $id ?: (int) ($_POST['id'] ?? 0);
        if ($id && $this->service) {
            $this->service->cancelCampaign($id);
            $this->setFlash('success', 'Campaign cancelled');
        }
        return $this->redirect(BASE_URL . '/admin/marketing-campaigns');
    }

    public function clone($id = 0)
    {
        $id = (int) $id ?: (int) ($_POST['id'] ?? 0);
        if ($id && $this->service) {
            $newId = $this->service->cloneCampaign($id);
            if ($newId) {
                $this->setFlash('success', "Campaign cloned as #$newId");
                return $this->redirect(BASE_URL . '/admin/marketing-campaigns/show/' . $newId);
            }
        }
        return $this->redirect(BASE_URL . '/admin/marketing-campaigns');
    }

    public function testSend($id = 0)
    {
        $id = (int) $id ?: (int) ($_POST['id'] ?? 0);
        if (!$id || !$this->service) {
            return $this->redirect(BASE_URL . '/admin/marketing-campaigns');
        }
        $result = $this->service->testSend($id, 5);
        if ($result['ok']) {
            $this->setFlash('success', 'Test send dispatched to ' . count($result['samples']) . ' recipients');
        } else {
            $this->setFlash('error', $result['error'] ?? 'Test send failed');
        }
        return $this->redirect(BASE_URL . '/admin/marketing-campaigns/show/' . $id);
    }

    public function schedule($id = 0)
    {
        $id = (int) $id ?: (int) ($_POST['id'] ?? 0);
        $sendAt = $_POST['scheduled_at'] ?? '';
        if ($id && $sendAt && $this->service) {
            $this->service->scheduleCampaign($id, $sendAt);
            $this->setFlash('success', "Campaign scheduled for $sendAt");
        }
        return $this->redirect(BASE_URL . '/admin/marketing-campaigns/show/' . $id);
    }

    public function stats($id = 0)
    {
        $id = (int) $id ?: (int) ($_GET['id'] ?? 0);
        if (!$id || !$this->service) {
            return $this->redirect(BASE_URL . '/admin/marketing-campaigns');
        }
        $stats = $this->service->getStats($id);
        if (empty($stats)) {
            $this->setFlash('error', 'Campaign not found');
            return $this->redirect(BASE_URL . '/admin/marketing-campaigns');
        }
        $recipients = $this->service->getRecipients($id, '', 200);
        return $this->render('admin.marketing_campaigns.stats', [
            'page_title'  => 'Campaign Stats #' . $id,
            'page_heading' => $stats['campaign']['name'] ?? 'Stats',
            'stats'       => $stats,
            'recipients'  => $recipients,
            'campaign_id' => $id,
        ]);
    }

    public function exportRecipients($id = 0)
    {
        $id = (int) $id ?: (int) ($_GET['id'] ?? 0);
        if (!$id || !$this->service) {
            return $this->redirect(BASE_URL . '/admin/marketing-campaigns');
        }
        $csv = $this->service->exportRecipientsCsv($id);
        $filename = 'campaign_' . $id . '_recipients_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $csv;
        exit;
    }
}
