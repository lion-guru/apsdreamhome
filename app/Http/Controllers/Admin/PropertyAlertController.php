<?php

namespace App\Http\Controllers\Admin;

use App\Services\PropertyAlertService;
use App\Services\AuditService;

/**
 * Property Alert Subscriptions Admin Controller
 * Manage customer property alert subscriptions
 */
class PropertyAlertController extends AdminController
{
    private $alerts;
    private $audit;

    public function __construct($db = null, $auth = null, array $config = [])
    {
        parent::__construct($db, $auth, $config);
        try {
            $this->alerts = new PropertyAlertService($this->db);
        } catch (\Throwable $e) {
            $this->alerts = null;
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
        if (is_object($db) && method_exists($db, 'getPdo')) {
            return $db->getPdo();
        }
        return $db;
    }

    public function index()
    {
        $stats = ['total' => 0, 'active' => 0, 'instant' => 0, 'daily' => 0, 'weekly' => 0, 'notifications_sent' => 0, 'top_property_types' => []];
        $subscriptions = [];
        $recent_notifications = [];
        if ($this->alerts) {
            $stats = $this->alerts->getStats();
            $subscriptions = $this->alerts->getActiveSubscriptions();
            try {
                $stmt = $this->getPdo()->query("SELECT pal.*, pas.email as sub_email, pas.name as sub_name FROM property_alert_log pal LEFT JOIN property_alert_subscriptions pas ON pas.id = pal.subscription_id ORDER BY pal.created_at DESC LIMIT 20");
                $recent_notifications = $stmt->fetchAll();
            } catch (\Throwable $e) {
                $recent_notifications = [];
            }
        }
        return $this->render('admin.property_alerts.index', [
            'page_title' => 'Property Alert Subscriptions',
            'page_heading' => 'Property Alerts',
            'stats' => $stats,
            'subscriptions' => $subscriptions,
            'recent_notifications' => $recent_notifications
        ]);
    }

    public function delete()
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0 && $this->alerts) {
            try {
                [$tenantSql, $tenantParams] = $this->tenantWhere();
                $stmt = $this->getPdo()->prepare("DELETE FROM property_alert_subscriptions WHERE id = ? $tenantSql");
                $stmt->execute(array_merge([$id], $tenantParams));
                if ($this->audit) {
                    $this->audit->log('property_alert.delete', $this->getUserId(), $this->getUserRole(), 'subscription', $id, "Deleted subscription #$id");
                }
                $this->setFlash('success', 'Subscription deleted successfully');
            } catch (\Throwable $e) {
                $this->setFlash('error', 'Failed to delete subscription');
            }
        }
        return $this->redirect(BASE_URL . '/admin/property-alerts');
    }

    public function toggle()
    {
        $id = (int)($_POST['id'] ?? 0);
        $active = (int)($_POST['active'] ?? 0);
        if ($id > 0 && $this->alerts) {
            try {
                [$tenantSql, $tenantParams] = $this->tenantWhere();
                $stmt = $this->getPdo()->prepare("UPDATE property_alert_subscriptions SET is_active = ? WHERE id = ? $tenantSql");
                $stmt->execute(array_merge([$active, $id], $tenantParams));
                $tid = $this->tenantId();
                $this->getPdo()->prepare("INSERT INTO property_alert_log (subscription_id, property_id, user_id, channel, status, message, tenant_id) VALUES (?, 0, NULL, 'admin', 'sent', ?, ?)")
                    ->execute([$id, $active ? 'Subscription activated' : 'Subscription paused', $tid]);
            } catch (\Throwable $e) {
                return $this->json(['error' => $e->getMessage()], 500);
            }
            return $this->json(['success' => true]);
        }
        return $this->json(['error' => 'Invalid id'], 400);
    }

    public function testMatch()
    {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id || !$this->alerts) {
            return $this->json(['error' => 'Invalid id'], 400);
        }
        $stmt = $this->getPdo()->prepare("SELECT * FROM property_alert_subscriptions WHERE id = ?");
        $stmt->execute([$id]);
        $sub = $stmt->fetch();
        if (!$sub) return $this->json(['error' => 'Not found'], 404);
        $matches = $this->alerts->findMatches($sub, 5);
        return $this->json(['success' => true, 'matches' => $matches, 'count' => count($matches)]);
    }
}
