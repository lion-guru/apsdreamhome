<?php

namespace App\Services\AI\Agents\specialized;

use App\Services\AI\Agents\BaseAgent;
use App\Services\AI\Modules\NLPProcessor;

/**
 * LeadGenerationAgent - Specialized agent for identifying and generating leads
 *
 * @property \App\Core\Database $db Inherited from BaseAgent
 */
class LeadGenerationAgent extends BaseAgent {
    /** @var NLPProcessor */
    private $nlp;

    public function __construct() {
        parent::__construct('LEAD_AGENT_001', 'Lead Generation Agent');
        $this->nlp = new NLPProcessor();
    }

    public function process($input, $context = []) {
        $text = $input['text'] ?? '';
        $sender = $input['sender'] ?? '';

        if (empty($text)) {
            return ['is_lead' => false, 'score' => 0];
        }

        // Use NLP for advanced analysis
        $analysis = $this->nlp->analyze($text);

        $score = 0;
        $intent = $analysis['intent']['name'];
        $confidence = $analysis['intent']['confidence'];
        $sentiment = $analysis['sentiment']['label'];

        // Intent-based scoring
        switch ($intent) {
            case 'enquiry':
                $score += 40 * $confidence;
                break;
            case 'investment':
                $score += 50 * $confidence;
                break;
            case 'appointment':
                $score += 60 * $confidence;
                break;
            case 'location':
            case 'amenities':
                $score += 30 * $confidence;
                break;
            case 'price':
                $score += 35 * $confidence;
                break;
        }

        // Sentiment adjustment
        if ($sentiment === 'positive') $score += 10;
        if ($sentiment === 'negative') $score -= 20;

        // Entity bonuses
        if (!empty($analysis['entities']['monetary'])) $score += 15;
        if (!empty($analysis['entities']['property_type'])) $score += 10;
        if (!empty($analysis['entities']['location'])) $score += 10;

        $is_lead = $score >= 40;

        if ($is_lead) {
            $this->generateLead($sender, $text, $score, $analysis);
            return [
                'is_lead' => true,
                'score' => round($score),
                'intent' => $intent,
                'analysis' => $analysis
            ];
        }

        return ['is_lead' => false, 'score' => round($score), 'intent' => $intent];
    }

    private function generateLead($phone, $message, $score, $analysis = []) {
        $this->logActivity("LEAD_GENERATED", "Lead from $phone with score $score", $analysis);

        try {
            // Check if lead already exists
            $row = $this->db->fetch("SELECT id FROM leads WHERE phone = ? LIMIT 1", [$phone]);
            $exists = !empty($row);

            $details = json_encode($analysis);

            if (!$exists) {
                // Create new lead
                $this->db->execute(
                    "INSERT INTO leads (phone, last_message, lead_score, status, ai_analysis) VALUES (?, ?, ?, 'new', ?)",
                    [$phone, $message, $score, $details]
                );

                // Notify Admin/Agent
                $this->notifyAdmin($phone, $message, $score, $analysis);
            } else {
                // Update existing lead score
                $this->db->execute(
                    "UPDATE leads SET lead_score = lead_score + ?, last_message = ?, ai_analysis = ?, updated_at = NOW() WHERE phone = ?",
                    [$score, $message, $details, $phone]
                );
            }
        } catch (Exception $e) {
            error_log("Error generating lead: " . $e->getMessage());
        }
    }

    private function notifyAdmin($phone, $message, $score, $analysis) {
        if (class_exists('WhatsAppIntegration')) {
            $wa = new WhatsAppIntegration();
            $adminPhone = $GLOBALS['config']['admin_phone'] ?? '';
            if ($adminPhone) {
                $intent = $analysis['intent']['name'] ?? 'unknown';
                $wa->sendMessage($adminPhone, "🚀 *New AI-Scored Lead!*\nPhone: $phone\nScore: $score\nIntent: $intent\nMessage: $message");
            }
        }
    }
}
