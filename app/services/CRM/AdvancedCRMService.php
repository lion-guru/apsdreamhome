<?php
namespace App\Services\CRM;

/**
 * Advanced CRM Service
 * Complete customer relationship management with AI integration
 */
class AdvancedCRMService
{
    private $database;
    
    public function __construct()
    {
        $this->database = \App\Core\Database\Database::getInstance();
    }
    
    /**
     * AI Lead Scoring
     */
    public function scoreLead($leadData)
    {
        $score = 0;
        
        // Budget score (30% weight)
        if (isset($leadData['budget'])) {
            $budget = (int)$leadData['budget'];
            if ($budget >= 10000000) $score += 30;
            elseif ($budget >= 5000000) $score += 20;
            elseif ($budget >= 2000000) $score += 10;
        }
        
        // Urgency score (25% weight)
        if (isset($leadData['urgency'])) {
            $urgency = strtolower($leadData['urgency']);
            if ($urgency === 'immediate') $score += 25;
            elseif ($urgency === 'within_month') $score += 20;
            elseif ($urgency === 'within_3_months') $score += 15;
            elseif ($urgency === 'within_6_months') $score += 10;
        }
        
        // Property type match (20% weight)
        if (isset($leadData['preferred_type'])) {
            $score += 20; // All types get base score
        }
        
        // Location preference (15% weight)
        if (isset($leadData['preferred_location'])) {
            $score += 15;
        }
        
        // Contact information completeness (10% weight)
        $completeness = 0;
        if (!empty($leadData['phone'])) $completeness += 3;
        if (!empty($leadData['email'])) $completeness += 3;
        if (!empty($leadData['name'])) $completeness += 2;
        if (!empty($leadData['message'])) $completeness += 2;
        
        $score += $completeness;
        
        return [
            'score' => min(100, $score),
            'rating' => $this->getLeadRating($score),
            'priority' => $this->getLeadPriority($score),
            'next_action' => $this->getNextAction($score)
        ];
    }
    
    /**
     * Get lead rating based on score
     */
    private function getLeadRating($score)
    {
        if ($score >= 80) return 'Hot';
        elseif ($score >= 60) return 'Warm';
        elseif ($score >= 40) return 'Cool';
        else return 'Cold';
    }
    
    /**
     * Get lead priority based on score
     */
    private function getLeadPriority($score)
    {
        if ($score >= 80) return 'High';
        elseif ($score >= 60) return 'Medium';
        else return 'Low';
    }
    
    /**
     * Get next action based on score
     */
    private function getNextAction($score)
    {
        if ($score >= 80) return 'Immediate Call';
        elseif ($score >= 60) return 'Schedule Meeting';
        elseif ($score >= 40) return 'Send Email Follow-up';
        else return 'Add to Nurturing Campaign';
    }
    
    /**
     * Automated follow-up sequence
     */
    public function createFollowUpSequence($leadId, $leadScore)
    {
        $sequence = [];
        
        if ($leadScore >= 80) {
            // Hot lead sequence
            $sequence = [
                ['delay' => 0, 'type' => 'call', 'message' => 'Immediate follow-up call'],
                ['delay' => 1, 'type' => 'email', 'message' => 'Thank you for your inquiry'],
                ['delay' => 3, 'type' => 'email', 'message' => 'Property recommendations'],
                ['delay' => 7, 'type' => 'call', 'message' => 'Follow-up call'],
                ['delay' => 14, 'type' => 'email', 'message' => 'New listings update']
            ];
        } elseif ($leadScore >= 60) {
            // Warm lead sequence
            $sequence = [
                ['delay' => 0, 'type' => 'email', 'message' => 'Welcome email'],
                ['delay' => 2, 'type' => 'call', 'message' => 'Introduction call'],
                ['delay' => 5, 'type' => 'email', 'message' => 'Property suggestions'],
                ['delay' => 10, 'type' => 'email', 'message' => 'Market update'],
                ['delay' => 21, 'type' => 'call', 'message' => 'Follow-up call']
            ];
        } else {
            // Cool/Cold lead sequence
            $sequence = [
                ['delay' => 0, 'type' => 'email', 'message' => 'Auto-response'],
                ['delay' => 3, 'type' => 'email', 'message' => 'Property guide'],
                ['delay' => 7, 'type' => 'email', 'message' => 'Market trends'],
                ['delay' => 14, 'type' => 'email', 'message' => 'Success stories'],
                ['delay' => 30, 'type' => 'email', 'message' => 'New listings']
            ];
        }
        
        // Save sequence to database
        foreach ($sequence as $step) {
            $stmt = $this->database->prepare("
                INSERT INTO follow_up_sequences (lead_id, delay_days, action_type, 
                message_content, scheduled_date, status) 
                VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL ? DAY), 'pending')
            ");
            
            $stmt->execute([
                $leadId,
                $step['delay'],
                $step['type'],
                $step['message'],
                $step['delay']
            ]);
        }
        
        return $sequence;
    }
    
    /**
     * Get lead analytics
     */
    public function getLeadAnalytics($timeframe = '30_days')
    {
        try {
            $interval = $timeframe === '7_days' ? '7 DAY' : 
                      ($timeframe === '30_days' ? '30 DAY' : '90 DAY');
            
            $stmt = $this->database->prepare("
                SELECT 
                    COUNT(*) as total_leads,
                    SUM(CASE WHEN score >= 80 THEN 1 ELSE 0 END) as hot_leads,
                    SUM(CASE WHEN score >= 60 AND score < 80 THEN 1 ELSE 0 END) as warm_leads,
                    SUM(CASE WHEN score < 60 THEN 1 ELSE 0 END) as cool_leads,
                    AVG(score) as avg_score,
                    DATE(created_at) as date
                FROM leads 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL {$interval})
                GROUP BY DATE(created_at)
                ORDER BY DATE(created_at) DESC
            ");
            
            $stmt->execute();
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * AI-powered lead recommendations
     */
    public function getLeadRecommendations($leadId)
    {
        try {
            // Get lead data
            $stmt = $this->database->prepare("
                SELECT * FROM leads WHERE id = ?
            ");
            $stmt->execute([$leadId]);
            $lead = $stmt->fetch();
            
            if (!$lead) return [];
            
            $recommendations = [];
            
            // Budget-based recommendations
            if ($lead['budget'] >= 10000000) {
                $recommendations[] = [
                    'type' => 'property',
                    'title' => 'Premium Properties',
                    'description' => 'Show luxury properties in prime locations',
                    'action' => 'filter_premium'
                ];
            }
            
            // Location-based recommendations
            if (!empty($lead['preferred_location'])) {
                $recommendations[] = [
                    'type' => 'location',
                    'title' => 'Local Market Insights',
                    'description' => 'Provide detailed analysis of ' . $lead['preferred_location'],
                    'action' => 'market_analysis'
                ];
            }
            
            // Urgency-based recommendations
            if ($lead['urgency'] === 'immediate') {
                $recommendations[] = [
                    'type' => 'urgent',
                    'title' => 'Quick Closing Properties',
                    'description' => 'Focus on properties ready for immediate possession',
                    'action' => 'filter_ready_to_move'
                ];
            }
            
            return $recommendations;
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Automated lead nurturing
     */
    public function nurtureLead($leadId, $action)
    {
        try {
            switch ($action) {
                case 'send_welcome':
                    $this->sendEmail($leadId, 'welcome', 'Welcome to APS Dream Home!');
                    break;
                    
                case 'send_property_suggestions':
                    $this->sendPropertySuggestions($leadId);
                    break;
                    
                case 'schedule_call':
                    $this->scheduleFollowUpCall($leadId);
                    break;
                    
                case 'update_lead_status':
                    $this->updateLeadStatus($leadId, 'nurtured');
                    break;
            }
            
            return true;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Send property suggestions to lead
     */
    private function sendPropertySuggestions($leadId)
    {
        // Get lead preferences
        $stmt = $this->database->prepare("
            SELECT * FROM leads WHERE id = ?
        ");
        $stmt->execute([$leadId]);
        $lead = $stmt->fetch();
        
        if (!$lead) return false;
        
        // Find matching properties
        $whereClause = "WHERE status = 'active'";
        $params = [];
        
        if (!empty($lead['budget'])) {
            $whereClause .= " AND price <= ?";
            $params[] = $lead['budget'];
        }
        
        if (!empty($lead['preferred_type'])) {
            $whereClause .= " AND property_type = ?";
            $params[] = $lead['preferred_type'];
        }
        
        if (!empty($lead['preferred_location'])) {
            $whereClause .= " AND location LIKE ?";
            $params[] = '%' . $lead['preferred_location'] . '%';
        }
        
        $stmt = $this->database->prepare("
            SELECT * FROM properties {$whereClause} 
            ORDER BY featured DESC, created_at DESC 
            LIMIT 5
        ");
        
        $stmt->execute($params);
        $properties = $stmt->fetchAll();
        
        // Send email with property suggestions
        $this->sendEmail($leadId, 'property_suggestions', 'Property Suggestions', ['properties' => $properties]);
        
        return true;
    }
    
    /**
     * Send email (placeholder for actual email service)
     */
    private function sendEmail($leadId, $template, $subject, $data = [])
    {
        // Log email sending
        $stmt = $this->database->prepare("
            INSERT INTO email_logs (lead_id, template, subject, data, sent_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $leadId,
            $template,
            $subject,
            json_encode($data)
        ]);
        
        return true;
    }
    
    /**
     * Schedule follow-up call
     */
    private function scheduleFollowUpCall($leadId)
    {
        $stmt = $this->database->prepare("
            INSERT INTO scheduled_calls (lead_id, call_type, scheduled_at, status) 
            VALUES (?, 'follow_up', DATE_ADD(NOW(), INTERVAL 1 DAY), 'scheduled')
        ");
        
        $stmt->execute([$leadId]);
        return true;
    }
    
    /**
     * Update lead status
     */
    private function updateLeadStatus($leadId, $status)
    {
        $stmt = $this->database->prepare("
            UPDATE leads SET status = ?, updated_at = NOW() WHERE id = ?
        ");
        
        $stmt->execute([$status, $leadId]);
        return true;
    }
}
