<?php

// TODO: Add proper error handling with try-catch blocks

namespace App\Services\AI;
/**
 * Communication Manager
 * Handles WhatsApp, Telegram, and Phone interactions with intelligent routing.
 */
class CommunicationManager {
    use \App\Traits\ServiceTenantTrait;

    private $db;
    private $aiManager;

    public function __construct($aiManager) {
        $this->db = \App\Core\Database\Database::getInstance();
        $this->aiManager = $aiManager;
    }

    /**
     * Log a new interaction and trigger routing
     */
    public function logInteraction($data) {
        $lead_id = $data['lead_id'] ?? null;
        $channel = $data['channel'];
        $type = $data['type'];
        $direction = $data['direction'];
        $content = $data['content'] ?? '';
        $recording = $data['recording_url'] ?? null;

try {
            $tenantData = $this->tenantInsertData();
            $tenantCols = array_keys($tenantData);
            $tenantVals = array_values($tenantData);
            $columns = array_merge(['lead_id', 'channel', 'interaction_type', 'direction', 'content', 'recording_url'], $tenantCols);
            $values  = array_merge([$lead_id, $channel, $type, $direction, $content, $recording], $tenantVals);
            $colStr = implode(', ', $columns);
            $placeholders = implode(', ', array_fill(0, count($values), '?'));
            $sql = "INSERT INTO communication_interactions ($colStr) VALUES ($placeholders)";
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        
        if ($this->db->execute($sql, array_merge([$lead_id, $channel, $type, $direction, $content, $recording], $tenantVals ?? []))) {
            $interaction_id = $this->db->lastInsertId();
            return $this->routeInteraction($interaction_id, $content);
        }
        return false;
    }

    /**
     * Intelligent Routing Logic
     */
    private function routeInteraction($interactionId, $content) {
        // Use AI to determine department and tag
        $analysis = $this->aiManager->executeTask(0, 'interaction_routing', ['content' => $content]);
        
        $tag = $analysis['output']['tag'] ?? 'enquiry';
        $deptType = $analysis['output']['department'] ?? 'sales';
        
try {
            $this->db->execute("UPDATE communication_interactions SET tag = ? WHERE id = ?" . $this->tenantSql(), array_merge([$tag, $interactionId], $this->tenantId() > 1 ? [$this->tenantId()] : []));
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }

        // Find best department and employee
        $dept = $this->db->fetch("SELECT id FROM departments WHERE type = ? LIMIT 1", [$deptType]);
        $deptId = $dept['id'] ?? null;

        if (!$deptId) return false;

        // Round-robin or Load-based employee assignment
        $employee = $this->db->fetch("SELECT employee_id FROM department_assignments WHERE department_id = ? AND is_available = 1 ORDER BY current_load ASC LIMIT 1", [$deptId]);
        $empId = $employee['employee_id'] ?? null;

$reason = "AI analyzed content as " . strtoupper($deptType) . " / " . strtoupper($tag);

        $tenantData = $this->tenantInsertData();
        $tenantCols = array_keys($tenantData);
        $tenantVals = array_values($tenantData);
        $columns = array_merge(['interaction_id', 'department_id', 'assigned_to', 'routing_reason'], $tenantCols);
        $values  = array_merge([$interactionId, $deptId, $empId, $reason], $tenantVals);
        $colStr = implode(', ', $columns);
        $placeholders = implode(', ', array_fill(0, count($values), '?'));
        $sql = "INSERT INTO interaction_routing ($colStr) VALUES ($placeholders)";
        if ($this->db->execute($sql, $values)) {
            $this->sendAlert($empId, $interactionId, $content);
            $this->setupFollowup($interactionId, $empId, $tag);
            return ['interaction_id' => $interactionId, 'assigned_to' => $empId, 'department' => $deptType];
        }
        return false;
    }

    private function sendAlert($empId, $interactionId, $content) {
        // Mock: Send notification to employee (WhatsApp/Telegram/System)
        // In real app, integrate with Twilio/Telegram Bot API
        return true;
    }

    private function setupFollowup($interactionId, $empId, $tag) {
        $delay = ($tag == 'investment') ? '+2 hours' : '+24 hours';
        $time = date('Y-m-d H:i:s', strtotime($delay));
        $msg = "Auto-followup for case #" . $interactionId . " (Tag: " . $tag . ")";
        
        $tenantData = $this->tenantInsertData();
        $tenantCols = array_keys($tenantData);
        $tenantVals = array_values($tenantData);
        $columns = array_merge(['interaction_id', 'employee_id', 'reminder_time', 'message'], $tenantCols);
        $values  = array_merge([$interactionId, $empId, $time, $msg], $tenantVals);
        $colStr = implode(', ', $columns);
        $placeholders = implode(', ', array_fill(0, count($values), '?'));
        $sql = "INSERT INTO interaction_reminders ($colStr) VALUES ($placeholders)";
        return $this->db->execute($sql, $values);
    }

    /**
     * Generate Documentation for Business Closure
     */
    public function generateClosureDocs($caseId, $type = 'investment_plan') {
        // Logic to generate PDF/Report based on interaction history
        $filePath = "uploads/docs/case_" . $caseId . "_" . $type . ".pdf";
        // Mock generation
        $tenantData = $this->tenantInsertData();
        $tenantCols = array_keys($tenantData);
        $tenantVals = array_values($tenantData);
        $columns = array_merge(['entity_type', 'entity_id', 'document_type', 'url', 'uploaded_on'], $tenantCols);
        $values  = array_merge(['business', $caseId, $type, $filePath], $tenantVals);
        $colStr = implode(', ', $columns);
        $placeholders = implode(', ', array_fill(0, count($values), '?'));
        $sql = "INSERT INTO documents ($colStr) VALUES ($placeholders)";
        return $this->db->execute($sql, $values);
    }
}
?>
