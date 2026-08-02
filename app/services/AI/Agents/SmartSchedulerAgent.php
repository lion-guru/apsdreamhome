<?php
/**
 * SmartSchedulerAgent — Optimizes site visits and agent scheduling
 * 
 * - Schedules site visits based on agent availability
 * - Optimizes route for multiple visits in a day
 * - Sends reminders before visits
 * - Reschedules cancelled visits
 * - Learns from completed visits to improve timing
 */

namespace App\Services\AI\Agents;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;

class SmartSchedulerAgent
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Schedule a new site visit intelligently
     */
    public function scheduleVisit(array $data): array
    {
        $leadId = $data['lead_id'];
        $preferredDate = $data['preferred_date'] ?? date('Y-m-d', strtotime('+1 day'));
        $preferredTime = $data['preferred_time'] ?? '10:00';
        $colonyId = $data['colony_id'] ?? null;

        // Find best available agent
        $agent = $this->findBestAgent($preferredDate, $preferredTime, $colonyId);

        if (!$agent) {
            return [
                'success' => false,
                'message' => 'No agents available at this time. Suggest alternative slots.',
                'alternatives' => $this->getAlternativeSlots($preferredDate, $colonyId),
            ];
        }

        // Create visit record
        $visitId = $this->createVisit($leadId, $agent['id'], $preferredDate, $preferredTime, $colonyId);

        // Send confirmation to lead
        $this->sendConfirmation($leadId, $preferredDate, $preferredTime, $agent['name']);

        // Send reminder to agent
        $this->sendAgentReminder($agent['id'], $leadId, $preferredDate, $preferredTime);

        return [
            'success' => true,
            'visit_id' => $visitId,
            'agent' => $agent['name'],
            'date' => $preferredDate,
            'time' => $preferredTime,
        ];
    }

    /**
     * Get optimized daily schedule for an agent
     */
    public function getDailySchedule(int $agentId, string $date): array
    {
        $visits = $this->db->fetchAll(
            "SELECT sv.*, l.name as lead_name, l.phone as lead_phone, c.name as colony_name
             FROM site_visits sv
             LEFT JOIN leads l ON sv.lead_id = l.id
             LEFT JOIN colonies c ON sv.colony_id = c.id
             WHERE sv.agent_id = ? AND sv.visit_date = ?
             ORDER BY sv.visit_time ASC",
            [$agentId, $date]
        ) ?: [];

        // Optimize route if multiple visits
        if (count($visits) > 1) {
            $visits = $this->optimizeRoute($visits);
        }

        return [
            'agent_id' => $agentId,
            'date' => $date,
            'visits' => $visits,
            'total' => count($visits),
            'next_visit' => $visits[0] ?? null,
        ];
    }

    /**
     * Auto-send reminders for upcoming visits
     */
    public function sendReminders(): array
    {
        // Visits tomorrow
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $visits = $this->db->fetchAll(
            "SELECT sv.*, l.name as lead_name, l.phone as lead_phone, u.name as agent_name
             FROM site_visits sv
             LEFT JOIN leads l ON sv.lead_id = l.id
             LEFT JOIN users u ON sv.agent_id = u.id
             WHERE sv.visit_date = ? AND sv.status = 'scheduled'",
            [$tomorrow]
        ) ?: [];

        $sent = 0;
        foreach ($visits as $v) {
            // Remind lead
            if (!empty($v['lead_phone'])) {
                $this->sendSMS($v['lead_phone'], "APS Dream Home: Aapka site visit kal {$v['visit_time']} baje hai. Location: {$v['colony_name']}. Agent: {$v['agent_name']}.");
            }
            // Remind agent
            $this->sendAgentReminder($v['agent_id'], $v['lead_id'], $v['visit_date'], $v['visit_time']);
            $sent++;
        }

        return ['reminders_sent' => $sent];
    }

    /**
     * Auto-reschedule missed/cancelled visits
     */
    public function autoReschedule(): array
    {
        $missed = $this->db->fetchAll(
            "SELECT * FROM site_visits WHERE status = 'scheduled' AND visit_date < CURDATE()" . $this->tenantSql()
        ) ?: [];

        $rescheduled = 0;
        foreach ($missed as $visit) {
            $newDate = date('Y-m-d', strtotime('+3 days'));
            $this->db->getConnection()->prepare(
                "UPDATE site_visits SET visit_date = ?, status = 'rescheduled', updated_at = NOW() WHERE id = ? AND lead_id = ?" . $this->tenantSql()
            )->execute([$newDate, $visit['id'], $visit['lead_id']]);

            // Notify lead
            if (!empty($visit['lead_id'])) {
                $this->db->getConnection()->prepare(
                    "INSERT INTO crm_interactions (lead_id, interaction_type, direction, content, created_at" . ( $this->tenantId() > 1 ? ', tenant_id' : '') . ")
                     VALUES (?, 'auto_reschedule', 'outbound', ?, NOW()" . ( $this->tenantId() > 1 ? ', ' . $this->tenantId() : '') . ")"
                )->execute([$visit['lead_id'], "Site visit rescheduled to $newDate. We'll contact you to confirm."]);
            }
            $rescheduled++;
        }

        return ['rescheduled' => $rescheduled];
    }

    // ─────── Helpers ─────────────────────────────────────────────────

    private function findBestAgent(string $date, string $time, ?int $colonyId): ?array
    {
        // Find agents with least visits on that day, preferably familiar with the colony
        return $this->db->fetch(
            "SELECT u.id, u.name,
                    (SELECT COUNT(*) FROM site_visits sv WHERE sv.agent_id = u.id AND sv.visit_date = ?" . $this->tenantSql('sv') . ") as today_visits,
                    (SELECT COUNT(*) FROM site_visits sv WHERE sv.agent_id = u.id AND sv.colony_id = ? AND sv.status = 'completed'" . $this->tenantSql('sv') . ") as colony_visits
             FROM users u
             WHERE u.role IN ('associate','agent','employee') AND u.is_active = 1" . $this->tenantSql('u') . "
             ORDER BY today_visits ASC, colony_visits DESC
             LIMIT 1",
            [$date, $colonyId ?? 0]
        );
    }

    private function getAlternativeSlots(string $date, ?int $colonyId): array
    {
        $slots = ['09:00', '10:00', '11:00', '12:00', '14:00', '15:00', '16:00'];
        $available = [];
        foreach ($slots as $slot) {
            $agent = $this->findBestAgent($date, $slot, $colonyId);
            if ($agent) $available[] = ['time' => $slot, 'agent' => $agent['name']];
        }
        return $available;
    }

    private function createVisit(int $leadId, int $agentId, string $date, string $time, ?int $colonyId): int
    {
        $this->db->getConnection()->prepare(
            "INSERT INTO site_visits (lead_id, agent_id, visit_date, visit_time, colony_id, status, created_at" . ( $this->tenantId() > 1 ? ', tenant_id' : '') . ")
             VALUES (?, ?, ?, ?, ?, 'scheduled', NOW()" . ( $this->tenantId() > 1 ? ', ' . $this->tenantId() : '') . ")"
        )->execute(array_merge([$leadId, $agentId, $date, $time, $colonyId], $this->tenantId() > 1 ? [$this->tenantId()] : []));
        return (int)$this->db->getConnection()->lastInsertId();
    }

    private function sendConfirmation(int $leadId, string $date, string $time, string $agentName): void
    {
        $lead = $this->db->fetch("SELECT phone, name FROM leads WHERE id = ?" . $this->tenantSql(), [$leadId]);
        if ($lead && !empty($lead['phone'])) {
            $this->sendSMS($lead['phone'], "APS Dream Home: Aapka site visit confirm ho gaya hai! 📅 Date: $date, Time: $time, Agent: $agentName. Address: Raghunath Nagri, Gorakhpur.");
        }
    }

    private function sendAgentReminder(int $agentId, int $leadId, string $date, string $time): void
    {
        try {
            $this->db->getConnection()->prepare(
                "INSERT INTO crm_tasks (lead_id, assigned_to, task_type, title, priority, due_date, status, created_at" . ( $this->tenantId() > 1 ? ', tenant_id' : '') . ")
                 VALUES (?, ?, 'site_visit_reminder', ?, 'high', ?, 'pending', NOW()" . ( $this->tenantId() > 1 ? ', ' . $this->tenantId() : '') . ")"
            )->execute(array_merge([$leadId, $agentId, "Site visit at $time — check plot availability", $date], $this->tenantId() > 1 ? [$this->tenantId()] : []));
        } catch (\Throwable $e) { /* non-critical */ error_log($e->getMessage()); }
    }

    private function optimizeRoute(array $visits): array
    {
        // Simple time-based ordering (geolocation optimization would need Google Maps API)
        usort($visits, fn($a, $b) => strcmp($a['visit_time'] ?? '', $b['visit_time'] ?? ''));
        return $visits;
    }

    private function sendSMS(string $phone, string $message): void
    {
        try {
            $this->db->getConnection()->prepare(
                "INSERT INTO sms_queue (phone, message, status, created_at" . ( $this->tenantId() > 1 ? ', tenant_id' : '') . ") VALUES (?, ?, 'pending', NOW()" . ( $this->tenantId() > 1 ? ', ' . $this->tenantId() : '') . ")"
            )->execute(array_merge([$phone, $message], $this->tenantId() > 1 ? [$this->tenantId()] : []));
        } catch (\Throwable $e) { /* non-critical */ error_log($e->getMessage()); }
    }
}
