<?php

namespace App\Services\AI;
/**
 * Unified Communication Manager
 * Handles WhatsApp, Telegram, and Phone interactions with intelligent routing.
 */
class UnifiedCommManager {
    private $db;
    private $aiManager;

    public function __construct($aiManager) {
        $this->db = \App\Core\App::database();
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

        $sql = "INSERT INTO communication_interactions (lead_id, channel, interaction_type, direction, content, recording_url) VALUES (?, ?, ?, ?, ?, ?)";
        
        if ($this->db->execute($sql, [$lead_id, $channel, $type, $direction, $content, $recording])) {
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
        
        // Update tag in interaction
        $this->db->execute("UPDATE communication_interactions SET tag = ? WHERE id = ?", [$tag, $interactionId]);

        // Find best department and employee
        $dept = $this->db->fetch("SELECT id FROM departments WHERE type = ? LIMIT 1", [$deptType]);
        $deptId = $dept['id'] ?? null;

        if (!$deptId) return false;

        // Round-robin or Load-based employee assignment
        $employee = $this->db->fetch("SELECT employee_id FROM department_assignments WHERE department_id = ? AND is_available = 1 ORDER BY current_load ASC LIMIT 1", [$deptId]);
        $empId = $employee['employee_id'] ?? null;

        $sql = "INSERT INTO interaction_routing (interaction_id, department_id, assigned_to, routing_reason) VALUES (?, ?, ?, ?)";
        $reason = "AI analyzed content as " . strtoupper($deptType) . " / " . strtoupper($tag);
        
        if ($this->db->execute($sql, [$interactionId, $deptId, $empId, $reason])) {
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
        
        $sql = "INSERT INTO interaction_reminders (interaction_id, employee_id, reminder_time, message) VALUES (?, ?, ?, ?)";
        return $this->db->execute($sql, [$interactionId, $empId, $time, $msg]);
    }

    /**
     * Generate Documentation for Business Closure
     */
    public function generateClosureDocs($caseId, $type = 'investment_plan') {
        // Logic to generate PDF/Report based on interaction history
        $filePath = "uploads/docs/case_" . $caseId . "_" . $type . ".pdf";
        // Mock generation
        $sql = "INSERT INTO business_documents (case_id, doc_type, file_path) VALUES (?, ?, ?)";
        return $this->db->execute($sql, [$caseId, $type, $filePath]);
    }
}
?>
