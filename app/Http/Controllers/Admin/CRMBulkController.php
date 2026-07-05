<?php

namespace App\Http\Controllers\Admin;

use App\Core\Database;

class CRMBulkController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();
        try {
            $db = Database::getInstance()->getConnection();
            $emailTemplates = $db->query("SELECT id, name, subject FROM email_templates ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            $smsTemplates = $db->query("SELECT id, name FROM sms_templates ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            $segments = $db->query("SELECT id, name FROM crm_segments ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            $recentCampaigns = $db->query("SELECT * FROM campaigns WHERE campaign_type IN ('email','sms') ORDER BY created_at DESC LIMIT 10")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            $emailTemplates = [];
            $smsTemplates = [];
            $segments = [];
            $recentCampaigns = [];
        }
        return $this->render('admin/crm/bulk/send', [
            'email_templates' => $emailTemplates,
            'sms_templates' => $smsTemplates,
            'segments' => $segments,
            'recent_campaigns' => $recentCampaigns,
            'page_title' => 'Bulk Email/SMS',
        ]);
    }

    public function preview()
    {
        $this->requireAdmin();
        try {
            $db = Database::getInstance()->getConnection();
            $channel = $_POST['channel'] ?? 'email';
            $segmentId = $_POST['segment_id'] ?? null;
            $templateId = $_POST['template_id'] ?? null;

            $where = ["l.deleted_at IS NULL", "l.status NOT IN ('converted','closed','dead')"];
            $params = [];
            if ($segmentId) {
                $seg = $db->query("SELECT filter_criteria FROM crm_segments WHERE id = $segmentId")->fetch(\PDO::FETCH_ASSOC);
                if ($seg && !empty($seg['filter_criteria'])) {
                    $criteria = json_decode($seg['filter_criteria'], true) ?? [];
                    if (!empty($criteria['status'])) { $where[] = "l.status = ?"; $params[] = $criteria['status']; }
                    if (!empty($criteria['source'])) { $where[] = "l.source = ?"; $params[] = $criteria['source']; }
                    if (!empty($criteria['min_score'])) { $where[] = "l.lead_score >= ?"; $params[] = (int)$criteria['min_score']; }
                    if (!empty($criteria['city'])) { $where[] = "l.city = ?"; $params[] = $criteria['city']; }
                }
            }

            $leads = $db->query("SELECT l.id, l.name, l.email, l.phone FROM leads l WHERE " . implode(' AND ', $where) . " ORDER BY l.created_at DESC LIMIT 500", $params)->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            header('Content-Type: application/json');
            echo json_encode([
                'total' => count($leads),
                'leads' => array_slice($leads, 0, 20),
                'sample' => count($leads) > 20 ? 'Showing 20 of ' . count($leads) : count($leads),
            ]);
        } catch (\Throwable $e) {
            header('Content-Type: application/json');
            echo json_encode(['total' => 0, 'leads' => [], 'error' => $e->getMessage()]);
        }
        exit;
    }

    public function send()
    {
        $this->requireAdmin();
        try {
            $db = Database::getInstance()->getConnection();
            $channel = $_POST['channel'] ?? 'email';
            $templateId = (int)($_POST['template_id'] ?? 0);
            $segmentId = $_POST['segment_id'] ?? null;
            $subject = trim($_POST['subject'] ?? '');
            $body = trim($_POST['body'] ?? '');

            // Get leads
            $where = ["l.deleted_at IS NULL", "l.status NOT IN ('converted','closed','dead')"];
            $params = [];
            if ($segmentId) {
                $seg = $db->query("SELECT filter_criteria FROM crm_segments WHERE id = $segmentId")->fetch(\PDO::FETCH_ASSOC);
                if ($seg && !empty($seg['filter_criteria'])) {
                    $criteria = json_decode($seg['filter_criteria'], true) ?? [];
                    if (!empty($criteria['status'])) { $where[] = "l.status = ?"; $params[] = $criteria['status']; }
                    if (!empty($criteria['source'])) { $where[] = "l.source = ?"; $params[] = $criteria['source']; }
                    if (!empty($criteria['min_score'])) { $where[] = "l.lead_score >= ?"; $params[] = (int)$criteria['min_score']; }
                }
            }

            $leads = $db->query("SELECT l.id, l.name, l.email, l.phone FROM leads l WHERE " . implode(' AND ', $where) . " ORDER BY l.created_at DESC LIMIT 500", $params)->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            $sent = 0;
            $failed = 0;
            foreach ($leads as $lead) {
                if ($channel === 'email' && !empty($lead['email'])) {
                    $personalizedBody = str_replace(['{{name}}', '{{phone}}'], [$lead['name'], $lead['phone'] ?? ''], $body);
                    try {
                        $db->query("INSERT INTO email_queue (to_email, subject, body, status, created_at) VALUES (?, ?, ?, 'queued', NOW())", [
                            $lead['email'], $subject, $personalizedBody
                        ]);
                        $sent++;
                    } catch (\Throwable $e) { $failed++; }
                } elseif ($channel === 'sms' && !empty($lead['phone'])) {
                    $personalizedBody = str_replace(['{{name}}', '{{phone}}'], [$lead['name'], $lead['phone'] ?? ''], $body);
                    try {
                        $db->query("INSERT INTO sms_queue (phone, message, status, created_at) VALUES (?, ?, 'queued', NOW())", [
                            $lead['phone'], $personalizedBody
                        ]);
                        $sent++;
                    } catch (\Throwable $e) { $failed++; }
                }
            }

            // Log campaign
            $db->query("INSERT INTO campaigns (name, campaign_type, status, recipient_count, sent_count, created_by, created_at) VALUES (?, ?, 'sent', ?, ?, ?, NOW())", [
                "Bulk " . ucfirst($channel) . " - " . date('d M Y H:i'),
                $channel,
                count($leads),
                $sent,
                $_SESSION['admin_id'] ?? 0,
            ]);

            $this->setFlash('success', "Bulk $channel sent: $sent queued, $failed failed");
        } catch (\Throwable $e) {
            error_log('CRMBulkController@send: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to send: ' . $e->getMessage());
        }
        return $this->redirect('/admin/crm/bulk-send');
    }
}
