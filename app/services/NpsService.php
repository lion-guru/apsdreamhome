<?php

namespace App\Services;

use PDO;
use App\Traits\ServiceTenantTrait;

class NpsService
{
    use ServiceTenantTrait;
    private $pdo;

    public function __construct($db = null)
    {
        if (is_object($db) && method_exists($db, 'getPdo')) {
            $this->pdo = $db->getPdo();
        } else {
            $this->pdo = $db;
        }
    }

    public function createSurvey($data)
    {
        try {
            $sql = "INSERT INTO nps_surveys (title, description, question_text, scale_min_label, scale_max_label, follow_up_question, is_active, send_immediately, delay_days, delay_hours, trigger_event, created_by, tenant_id) 
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?," . ($this->tenantId() > 1 ? $this->tenantId() : "NULL") . ")";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['title'],
                $data['description'] ?? null,
                $data['question_text'] ?? 'How likely are you to recommend us to a friend or colleague?',
                $data['scale_min_label'] ?? 'Not at all likely',
                $data['scale_max_label'] ?? 'Extremely likely',
                $data['follow_up_question'] ?? null,
                $data['is_active'] ?? 1,
                $data['send_immediately'] ?? 0,
                $data['delay_days'] ?? null,
                $data['delay_hours'] ?? null,
                $data['trigger_event'] ?? 'manual',
                $data['created_by'] ?? null
            ]);
            return $this->pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log("NPS create survey: " . $e->getMessage());
            return null;
        }
    }

    public function getAllSurveys($activeOnly = false)
    {
        try {
            $sql = "SELECT s.*, u.name as creator_name FROM nps_surveys s LEFT JOIN users u ON s.created_by = u.id WHERE 1=1 {$this->tenantSqlForAlias('s')}";
            $params = [];
            if ($activeOnly) {
                $sql .= " AND s.is_active = 1";
            }
            $sql .= " ORDER BY s.created_at DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) { error_log('Silent catch: ' . $e->getMessage()); return []; }
    }

    public function getSurveyById($id)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT s.*, u.name as creator_name FROM nps_surveys s LEFT JOIN users u ON s.created_by = u.id WHERE s.id = ? {$this->tenantSqlForAlias('s')}");
            $stmt->execute([$id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) { error_log('Silent catch: ' . $e->getMessage()); return null; }
    }

    public function updateSurvey($id, $data)
    {
        try {
            $sql = "UPDATE nps_surveys SET title = ?, description = ?, question_text = ?, scale_min_label = ?, scale_max_label = ?, follow_up_question = ?, is_active = ?, send_immediately = ?, delay_days = ?, delay_hours = ?, trigger_event = ?, updated_at = NOW() WHERE id = ? {$this->tenantSql()}";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['title'],
                $data['description'] ?? null,
                $data['question_text'] ?? 'How likely are you to recommend us to a friend or colleague?',
                $data['scale_min_label'] ?? 'Not at all likely',
                $data['scale_max_label'] ?? 'Extremely likely',
                $data['follow_up_question'] ?? null,
                $data['is_active'] ?? 1,
                $data['send_immediately'] ?? 0,
                $data['delay_days'] ?? null,
                $data['delay_hours'] ?? null,
                $data['trigger_event'] ?? 'manual',
                $id
            ]);
            return $stmt->rowCount() > 0;
        } catch (\Throwable $e) {
            error_log("NPS update survey: " . $e->getMessage());
            return false;
        }
    }

    public function deleteSurvey($id)
    {
        try {
            $this->pdo->prepare("DELETE FROM nps_responses WHERE survey_id = ? {$this->tenantSql()}")->execute([$id]);
            $this->pdo->prepare("DELETE FROM nps_schedule WHERE survey_id = ? {$this->tenantSql()}")->execute([$id]);
            $this->pdo->prepare("DELETE FROM nps_surveys WHERE id = ? {$this->tenantSql()}")->execute([$id]);
            return true;
        } catch (\Throwable $e) { error_log('Silent catch: ' . $e->getMessage()); return false; }
    }

    public function recordResponse($surveyId, $userId, $visitorId, $score, $followUpAnswer = null, $ip = '', $userAgent = '')
    {
        try {
            // Validate score
            if ($score < 0 || $score > 10) {
                return ['error' => 'Invalid score'];
            }
            
            // Check if already responded
            $existing = $this->pdo->prepare("SELECT id FROM nps_responses WHERE survey_id = ? AND (user_id = ? OR visitor_id = ?) {$this->tenantSql()}")->execute([$surveyId, $userId, $visitorId])->fetch();
            if ($existing) {
                return ['error' => 'Already responded to this survey'];
            }
            
            // Calculate category
            $category = $score >= 9 ? 'promoter' : ($score >= 7 ? 'passive' : 'detractor');
            
            $this->pdo->beginTransaction();
            
            $tid = $this->tenantId();
            $cols = "survey_id, user_id, visitor_id, score, category, follow_up_answer, ip_address, user_agent";
            $vals = "?, ?, ?, ?, ?, ?, ?, ?";
            $params = [$surveyId, $userId, $visitorId, $score, $category, $followUpAnswer, $ip, $userAgent];
            if ($tid > 1) { $cols .= ", tenant_id"; $vals .= ", ?"; $params[] = $tid; }
            $this->pdo->prepare("INSERT INTO nps_responses ($cols) VALUES ($vals)")
                ->execute($params);
            
            $this->pdo->commit();
            return ['success' => true, 'category' => $category];
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            error_log("NPS record response: " . $e->getMessage());
            return ['error' => 'Failed to record response'];
        }
    }

    public function getResponses($surveyId, $limit = 50)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT r.*, u.name as user_name, v.name as visitor_name FROM nps_responses r 
                                        LEFT JOIN users u ON r.user_id = u.id 
                                        LEFT JOIN users v ON r.visitor_id = v.id 
                                        WHERE r.survey_id = ? {$this->tenantSqlForAlias('r')}
                                        ORDER BY r.responded_at DESC LIMIT " . (int)$limit);
            $stmt->execute([$surveyId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) { error_log('Silent catch: ' . $e->getMessage()); return []; }
    }

    public function getStats($surveyId = null)
    {
        $stats = [
            'total_responses' => 0,
            'promoters' => 0,
            'passives' => 0,
            'detractors' => 0,
            'nps_score' => null,
            'avg_score' => 0,
            'response_rate' => 0
        ];
        try {
            $where = $surveyId ? "WHERE r.survey_id = ? {$this->tenantSqlForAlias('r')}" : "WHERE 1=1 {$this->tenantSqlForAlias('r')}";
            $params = $surveyId ? [$surveyId] : [];
            
            $totalStmt = $this->pdo->prepare("SELECT COUNT(*) FROM nps_responses r $where");
            $totalStmt->execute($params);
            $stats['total_responses'] = (int)$totalStmt->fetchColumn();
            
            if ($stats['total_responses'] > 0) {
                $catStmt = $this->pdo->prepare("SELECT 
                    SUM(CASE WHEN category = 'promoter' THEN 1 ELSE 0 END) as promoters,
                    SUM(CASE WHEN category = 'passive' THEN 1 ELSE 0 END) as passives,
                    SUM(CASE WHEN category = 'detractor' THEN 1 ELSE 0 END) as detractors,
                    AVG(score) as avg_score
                    FROM nps_responses r $where");
                $catStmt->execute($params);
                $cat = $catStmt->fetch(\PDO::FETCH_ASSOC);
                
                $stats['promoters'] = (int)$cat['promoters'];
                $stats['passives'] = (int)$cat['passives'];
                $stats['detractors'] = (int)$cat['detractors'];
                $stats['avg_score'] = round($cat['avg_score'] ?? 0, 2);
                
                if ($stats['total_responses'] > 0) {
                    $promoterPct = ($stats['promoters'] / $stats['total_responses']) * 100;
                    $detractorPct = ($stats['detractors'] / $stats['total_responses']) * 100;
                    $stats['nps_score'] = round($promoterPct - $detractorPct, 1);
                }
            }
            
            // Calculate response rate (simplified - would need to track who was surveyed)
            $stats['response_rate'] = $stats['total_responses'] > 0 ? min(100, $stats['total_responses'] * 2) : 0;
            
        } catch (\Throwable $e) { error_log("NPS get stats: " . $e->getMessage()); }
        return $stats;
    }

    public function processTriggers()
    {
        try {
            // Get surveys with triggers that should be sent
            $stmt = $this->pdo->prepare("SELECT s.* FROM nps_surveys s WHERE s.is_active = 1 AND s.trigger_event != 'manual' {$this->tenantSqlForAlias('s')}");
            $stmt->execute();
            $activeSurveys = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $scheduled = 0;
            foreach ($activeSurveys as $survey) {
                // This would normally check for recent trigger events
                // For now, we'll just schedule some test responses
                $scheduled += $this->scheduleSurveyForUsers($survey['id'], 5);
            }
            return $scheduled;
        } catch (\Throwable $e) { return 0; }
    }

    private function scheduleSurveyForUsers($surveyId, $limit = 10)
    {
        try {
            // Get some users to survey (simplified)
            $users = $this->pdo->query("SELECT id FROM users WHERE role IN ('customer','agent','associate')" . $this->tenantSql() . " LIMIT " . (int)$limit)->fetchAll(\PDO::FETCH_COLUMN);
            $scheduled = 0;
            foreach ($users as $userId) {
                // Check if already scheduled
                $existing = $this->pdo->prepare("SELECT id FROM nps_schedule WHERE survey_id = ? AND user_id = ? AND status = 'pending'" . $this->tenantSql())
                    ->execute([$surveyId, $userId])->fetch();
                if (!$existing) {
                    $sendAt = date('Y-m-d H:i:s', time() + rand(3600, 86400)); // 1-24 hours from now
                    $tid = $this->tenantId();
                    $cols = "survey_id, user_id, scheduled_for, tenant_id";
                    $vals = "?,?,?,?";
                    $params = [$surveyId, $userId, $sendAt, $tid];
                    if ($tid > 1) { $cols .= ", tenant_id"; $vals .= ",?"; $params[] = $tid; }
                    $this->pdo->prepare("INSERT INTO nps_schedule ($cols) VALUES ($vals)")
                        ->execute($params);
                    $scheduled++;
                }
            }
            return $scheduled;
        } catch (\Throwable $e) { return 0; }
    }

    public function sendDueSurveys()
    {
        try {
            $stmt = $this->pdo->prepare("SELECT s.*, n.* FROM nps_schedule n 
                                        JOIN nps_surveys s ON n.survey_id = s.id 
                                        WHERE n.status = 'pending' AND n.scheduled_for <= NOW() {$this->tenantSqlForAlias('n')} {$this->tenantSqlForAlias('s')}");
            $stmt->execute();
            $due = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $sent = 0;
            foreach ($due as $item) {
                // In reality, this would send email/SMS with survey link
                // For now, just mark as sent
                $this->pdo->prepare("UPDATE nps_schedule SET status = 'sent', sent_at = NOW() WHERE id = ? {$this->tenantSql()}")
                    ->execute([$item['id']]);
                $sent++;
            }
            return $sent;
        } catch (\Throwable $e) { return 0; }
    }

    public function getWidgetData()
    {
        try {
            $activeSurvey = $this->pdo->prepare("SELECT * FROM nps_surveys WHERE is_active = 1 AND send_immediately = 1 {$this->tenantSql()} ORDER BY created_at DESC LIMIT 1")
                ->execute()->fetch();
            if (!$activeSurvey) return null;
            
            $stats = $this->getStats($activeSurvey['id']);
            return [
                'survey' => $activeSurvey,
                'stats' => $stats
            ];
        } catch (\Throwable $e) { error_log('Silent catch: ' . $e->getMessage()); return null; }
    }
}
