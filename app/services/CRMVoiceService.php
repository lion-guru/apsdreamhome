<?php
/**
 * CRMVoiceService — Voice CRM integration (calls, dictation, voice commands)
 */
namespace App\Services;

use App\Core\Database;
use \App\Traits\ServiceTenantTrait;

class CRMVoiceService
{
    use \App\Traits\ServiceTenantTrait;

    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function logVoiceCall($leadId, $userId, $duration, $notes, $outcome = 'completed') {
        try {
            require_once __DIR__ . '/CRMService.php';
            $crm = new CRMService();
            $crm->addInteraction($leadId, $userId, 'call', [
                'subject' => 'Voice call',
                'body' => $notes,
                'duration_seconds' => $duration,
                'outcome' => $outcome,
            ]);
            $crm->logActivity($leadId, 'call', "Voice call ($duration sec): $notes", null, null, $userId);
            return ['success' => true];
        } catch (\Exception $e) { return ['success' => false, 'error' => $e->getMessage()]; }
    }

    public function saveVoiceNote($leadId, $userId, $transcript, $tags = '') {
        try {
            require_once __DIR__ . '/CRMService.php';
            $crm = new CRMService();
            $crm->addInteraction($leadId, $userId, 'note', [
                'subject' => 'Voice note',
                'body' => $transcript,
            ]);
            $crm->logActivity($leadId, 'note', "Voice note: " . substr($transcript, 0, 100), null, null, $userId);
            return ['success' => true];
        } catch (\Exception $e) { return ['success' => false, 'error' => $e->getMessage()]; }
    }

    public function processVoiceCommand($command, $userId) {
        $cmd = strtolower(trim($command));
        $result = ['action' => 'unknown', 'message' => 'Command not recognized'];

        // Hindi commands
        if (strpos($cmd, 'अगली बैठक') !== false || strpos($cmd, 'next meeting') !== false) {
            $meetings = (new MeetingService())->getUpcomingMeetings($userId, 1);
            if ($meetings) {
                $m = $meetings[0];
                $result = ['action' => 'next_meeting', 'message' => "Next meeting: {$m['title']} with {$m['lead_name']} at {$m['start_time']}"];
            } else {
                $result = ['action' => 'next_meeting', 'message' => 'No upcoming meetings'];
            }
        } elseif (strpos($cmd, 'हॉट लीड') !== false || strpos($cmd, 'hot lead') !== false) {
            $db = Database::getInstance()->getConnection();
            $hot = $db->query("SELECT COUNT(*) as cnt FROM leads WHERE lead_score >= 70 AND deleted_at IS NULL AND status NOT IN ('converted','closed','dead')")->fetchColumn();
            $result = ['action' => 'hot_leads', 'message' => "$hot hot leads available"];
        } elseif (strpos($cmd, 'नोट') !== false && strpos($cmd, 'जोड़') !== false) {
            $result = ['action' => 'add_note', 'message' => 'Please dictate your note'];
        } elseif (strpos($cmd, 'कॉल') !== false || strpos($cmd, 'call') !== false) {
            $result = ['action' => 'make_call', 'message' => 'Opening call interface...'];
        } elseif (strpos($cmd, 'अनुसूची') !== false || strpos($cmd, 'schedule') !== false) {
            $result = ['action' => 'schedule', 'message' => 'Opening scheduler...'];
        } elseif (strpos($cmd, 'रिपोर्ट') !== false || strpos($cmd, 'report') !== false) {
            $result = ['action' => 'report', 'message' => 'Generating daily report...'];
        }

        return $result;
    }

    public function getRecentVoiceActivity($userId, $limit = 20) {
        try {
            return $this->db->fetchAll(
                "SELECT ci.*, l.name as lead_name FROM crm_interactions ci
                 LEFT JOIN leads l ON l.id = ci.lead_id
                 WHERE ci.user_id = ? AND ci.interaction_type IN ('call','note')
                 ORDER BY ci.created_at DESC LIMIT ?",
                [$userId, $limit]
            ) ?: [];
        } catch (\Exception $e) { error_log('Silent catch: ' . $e->getMessage()); return []; }
    }

    public function getVoiceStats() {
        try {
            $stats = $this->db->fetch(
                "SELECT COUNT(*) as total_calls,
                    SUM(CASE WHEN interaction_type = 'call' THEN 1 ELSE 0 END) as calls,
                    SUM(CASE WHEN interaction_type = 'note' THEN 1 ELSE 0 END) as notes,
                    SUM(CASE WHEN interaction_type = 'call' THEN CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(metadata, '\"duration_seconds\":', -1), ',', 1) AS UNSIGNED) ELSE 0 END) as total_duration
                 FROM crm_interactions WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
            );
            return $stats ?: ['total_calls' => 0, 'calls' => 0, 'notes' => 0, 'total_duration' => 0];
        } catch (\Exception $e) { return ['total_calls' => 0, 'calls' => 0, 'notes' => 0, 'total_duration' => 0]; }
    }
}
