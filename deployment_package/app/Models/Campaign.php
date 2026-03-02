<?php

namespace App\Models;

use App\Core\Database;
use DateTime;

/**
 * Automated Follow-ups and Drip Campaigns Model
 * Handles marketing automation, drip campaigns, and automated follow-ups
 */
class Campaign extends Model
{
    protected $table = 'campaigns';

    const STATUS_DRAFT = 'draft';
    const STATUS_ACTIVE = 'active';
    const STATUS_PAUSED = 'paused';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    const TYPE_DRIP = 'drip';
    const TYPE_FOLLOWUP = 'followup';
    const TYPE_NURTURE = 'nurture';
    const TYPE_REENGAGEMENT = 'reengagement';
    const TYPE_ANNOUNCEMENT = 'announcement';

    const SCHEDULE_IMMEDIATE = 'immediate';
    const SCHEDULE_SCHEDULED = 'scheduled';
    const SCHEDULE_EVENT_TRIGGERED = 'event_triggered';
    const SCHEDULE_BEHAVIOR_TRIGGERED = 'behavior_triggered';

    /**
     * Create a new campaign
     */
    public function createCampaign(array $campaignData): array
    {
        $campaignRecord = [
            'campaign_name' => $campaignData['campaign_name'],
            'campaign_type' => $campaignData['campaign_type'] ?? self::TYPE_DRIP,
            'campaign_description' => $campaignData['campaign_description'] ?? null,
            'target_audience' => json_encode($campaignData['target_audience'] ?? []),
            'status' => self::STATUS_DRAFT,
            'schedule_type' => $campaignData['schedule_type'] ?? self::SCHEDULE_SCHEDULED,
            'start_date' => $campaignData['start_date'] ?? null,
            'end_date' => $campaignData['end_date'] ?? null,
            'timezone' => $campaignData['timezone'] ?? 'Asia/Kolkata',
            'budget_limit' => $campaignData['budget_limit'] ?? null,
            'cost_per_send' => $campaignData['cost_per_send'] ?? null,
            'created_by' => $campaignData['created_by'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $campaignId = $this->insert($campaignRecord);

        return [
            'success' => true,
            'campaign_id' => $campaignId,
            'message' => 'Campaign created successfully'
        ];
    }

    /**
     * Add sequence to campaign
     */
    public function addSequence(array $sequenceData): array
    {
        $sequenceRecord = [
            'campaign_id' => $sequenceData['campaign_id'],
            'sequence_name' => $sequenceData['sequence_name'],
            'sequence_order' => $sequenceData['sequence_order'] ?? 0,
            'delay_days' => $sequenceData['delay_days'] ?? 0,
            'delay_hours' => $sequenceData['delay_hours'] ?? 0,
            'delay_minutes' => $sequenceData['delay_minutes'] ?? 0,
            'trigger_event' => $sequenceData['trigger_event'] ?? null,
            'condition_rules' => json_encode($sequenceData['condition_rules'] ?? []),
            'is_active' => $sequenceData['is_active'] ?? 1,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $sequenceId = $this->insertInto('campaign_sequences', $sequenceRecord);

        return [
            'success' => true,
            'sequence_id' => $sequenceId,
            'message' => 'Sequence added successfully'
        ];
    }

    /**
     * Add message to sequence
     */
    public function addSequenceMessage(array $messageData): array
    {
        $messageRecord = [
            'sequence_id' => $messageData['sequence_id'],
            'message_name' => $messageData['message_name'],
            'channels' => json_encode($messageData['channels'] ?? ['email']),
            'subject' => $messageData['subject'] ?? null,
            'content' => $messageData['content'],
            'template_id' => $messageData['template_id'] ?? null,
            'attachments' => json_encode($messageData['attachments'] ?? []),
            'priority' => $messageData['priority'] ?? 'normal',
            'track_opens' => $messageData['track_opens'] ?? 1,
            'track_clicks' => $messageData['track_clicks'] ?? 1,
            'a_b_test_enabled' => $messageData['a_b_test_enabled'] ?? 0,
            'a_b_test_percentage' => $messageData['a_b_test_percentage'] ?? 50.00,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $messageId = $this->insertInto('sequence_messages', $messageRecord);

        return [
            'success' => true,
            'message_id' => $messageId,
            'message' => 'Message added successfully'
        ];
    }

    /**
     * Start campaign
     */
    public function startCampaign(int $campaignId): array
    {
        $campaign = $this->find($campaignId);
        if (!$campaign) {
            return ['success' => false, 'message' => 'Campaign not found'];
        }

        if ($campaign['status'] !== self::STATUS_DRAFT) {
            return ['success' => false, 'message' => 'Campaign is not in draft status'];
        }

        // Update campaign status
        $this->update($campaignId, [
            'status' => self::STATUS_ACTIVE,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Add recipients based on target audience
        $recipientsAdded = $this->addCampaignRecipients($campaignId, json_decode($campaign['target_audience'], true));

        return [
            'success' => true,
            'message' => 'Campaign started successfully',
            'recipients_added' => $recipientsAdded
        ];
    }

    /**
     * Process campaign sequences
     */
    public function processCampaignSequences(): array
    {
        $db = Database::getInstance();

        // Get active campaigns
        $activeCampaigns = $this->query(
            "SELECT * FROM campaigns WHERE status = ? AND (end_date IS NULL OR end_date > NOW())",
            [self::STATUS_ACTIVE]
        )->fetchAll();

        $processedSequences = 0;
        $messagesSent = 0;

        foreach ($activeCampaigns as $campaign) {
            $sequencesProcessed = $this->processCampaignSequencesForCampaign($campaign);
            $processedSequences += $sequencesProcessed['sequences'];
            $messagesSent += $sequencesProcessed['messages'];
        }

        return [
            'success' => true,
            'processed_sequences' => $processedSequences,
            'messages_sent' => $messagesSent,
            'message' => "Processed {$processedSequences} sequences, sent {$messagesSent} messages"
        ];
    }

    /**
     * Process sequences for a specific campaign
     */
    private function processCampaignSequencesForCampaign(array $campaign): array
    {
        $sequences = $this->getCampaignSequences($campaign['id']);
        $sequencesProcessed = 0;
        $messagesSent = 0;

        foreach ($sequences as $sequence) {
            if (!$sequence['is_active']) continue;

            $messagesInSequence = $this->getSequenceMessages($sequence['id']);

            foreach ($messagesInSequence as $message) {
                $sent = $this->processMessageForSequence($campaign, $sequence, $message);
                if ($sent > 0) {
                    $messagesSent += $sent;
                }
            }

            $sequencesProcessed++;
        }

        return [
            'sequences' => $sequencesProcessed,
            'messages' => $messagesSent
        ];
    }

    /**
     * Process message for sequence
     */
    private function processMessageForSequence(array $campaign, array $sequence, array $message): int
    {
        $db = Database::getInstance();

        // Calculate when this message should be sent
        $sendTime = $this->calculateMessageSendTime($campaign, $sequence);

        if ($sendTime > time()) {
            return 0; // Not yet time to send
        }

        // Get recipients who haven't received this message yet
        $recipients = $db->query(
            "SELECT cr.* FROM campaign_recipients cr
             WHERE cr.campaign_id = ? AND cr.status = 'pending'
             LIMIT 100", // Process in batches
            [$campaign['id']]
        )->fetchAll();

        $sent = 0;
        foreach ($recipients as $recipient) {
            $result = $this->sendMessageToRecipient($message, $recipient);
            if ($result['success']) {
                $sent++;

                // Update recipient status
                $db->query(
                    "UPDATE campaign_recipients SET status = 'sent', sent_at = NOW() WHERE id = ?",
                    [$recipient['id']]
                );
            }
        }

        return $sent;
    }

    /**
     * Send message to recipient
     */
    private function sendMessageToRecipient(array $message, array $recipient): array
    {
        $channels = json_decode($message['channels'], true);
        $content = $this->personalizeContent($message['content'], $recipient);
        $subject = $message['subject'] ? $this->personalizeContent($message['subject'], $recipient) : null;

        $results = [];
        foreach ($channels as $channel) {
            switch ($channel) {
                case 'email':
                    $results[$channel] = $this->sendEmail($recipient, $subject, $content, $message);
                    break;
                case 'sms':
                    $results[$channel] = $this->sendSMS($recipient, $content);
                    break;
                case 'whatsapp':
                    $results[$channel] = $this->sendWhatsApp($recipient, $content);
                    break;
                case 'push':
                    $results[$channel] = $this->sendPush($recipient, $subject, $content);
                    break;
            }
        }

        // Return success if at least one channel succeeded
        $success = !empty(array_filter($results, function($result) {
            return $result['success'] ?? false;
        }));

        return [
            'success' => $success,
            'results' => $results
        ];
    }

    /**
     * Personalize message content
     */
    private function personalizeContent(string $content, array $recipient): string
    {
        $replacements = [
            '{lead_name}' => $this->getRecipientName($recipient),
            '{email}' => $recipient['email'] ?? '',
            '{phone}' => $recipient['phone'] ?? '',
            '{campaign_name}' => 'Property Campaign', // Would come from campaign data
            '{unsubscribe_link}' => $this->generateUnsubscribeLink($recipient),
            '{current_date}' => date('d/m/Y'),
            '{current_time}' => date('H:i')
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    /**
     * Calculate message send time
     */
    private function calculateMessageSendTime(array $campaign, array $sequence): int
    {
        $baseTime = strtotime($campaign['start_date'] ?? 'now');

        $delaySeconds = ($sequence['delay_days'] * 24 * 60 * 60) +
                       ($sequence['delay_hours'] * 60 * 60) +
                       ($sequence['delay_minutes'] * 60);

        return $baseTime + $delaySeconds;
    }

    /**
     * Add campaign recipients
     */
    private function addCampaignRecipients(int $campaignId, array $targetCriteria): int
    {
        $db = Database::getInstance();

        // Build query based on target criteria
        $query = "SELECT DISTINCT ";
        $params = [];

        if (!empty($targetCriteria['include_leads'])) {
            $query .= "id as lead_id, NULL as customer_id, 'lead' as user_type, email, phone FROM leads WHERE 1=1";
        } elseif (!empty($targetCriteria['include_customers'])) {
            $query .= "NULL as lead_id, id as customer_id, 'customer' as user_type, email, phone FROM users WHERE 1=1";
        } else {
            // Default to leads
            $query .= "id as lead_id, NULL as customer_id, 'lead' as user_type, email, phone FROM leads WHERE 1=1";
        }

        // Add filters based on criteria
        if (!empty($targetCriteria['lead_source'])) {
            $query .= " AND lead_source = ?";
            $params[] = $targetCriteria['lead_source'];
        }

        if (!empty($targetCriteria['min_score'])) {
            $query .= " AND id IN (SELECT lead_id FROM lead_scores WHERE current_score >= ?)";
            $params[] = $targetCriteria['min_score'];
        }

        if (!empty($targetCriteria['property_types'])) {
            $query .= " AND property_types LIKE ?";
            $params[] = '%' . implode('%', $targetCriteria['property_types']) . '%';
        }

        $query .= " LIMIT 1000"; // Limit for performance

        $potentialRecipients = $db->query($query, $params)->fetchAll();

        $added = 0;
        foreach ($potentialRecipients as $recipient) {
            $db->query(
                "INSERT IGNORE INTO campaign_recipients
                 (campaign_id, lead_id, customer_id, user_type, email, phone)
                 VALUES (?, ?, ?, ?, ?, ?)",
                [
                    $campaignId,
                    $recipient['lead_id'],
                    $recipient['customer_id'],
                    $recipient['user_type'],
                    $recipient['email'] ?? null,
                    $recipient['phone'] ?? null
                ]
            );
            $added++;
        }

        // Update campaign total recipients
        $this->update($campaignId, ['total_recipients' => $added]);

        return $added;
    }

    /**
     * Get campaign sequences
     */
    private function getCampaignSequences(int $campaignId): array
    {
        return $this->query(
            "SELECT * FROM campaign_sequences WHERE campaign_id = ? ORDER BY sequence_order ASC",
            [$campaignId]
        )->fetchAll();
    }

    /**
     * Get sequence messages
     */
    private function getSequenceMessages(int $sequenceId): array
    {
        return $this->query(
            "SELECT * FROM sequence_messages WHERE sequence_id = ? ORDER BY created_at ASC",
            [$sequenceId]
        )->fetchAll();
    }

    /**
     * Get campaign analytics
     */
    public function getCampaignAnalytics(int $campaignId, string $period = '30 days'): array
    {
        $startDate = date('Y-m-d', strtotime("-{$period}"));

        $analytics = $this->query(
            "SELECT
                SUM(sent_count) as total_sent,
                SUM(delivered_count) as total_delivered,
                SUM(open_count) as total_opens,
                SUM(click_count) as total_clicks,
                SUM(conversion_count) as total_conversions,
                SUM(bounce_count) as total_bounces,
                SUM(unsubscribe_count) as total_unsubscribes,
                AVG(CASE WHEN sent_count > 0 THEN (open_count / sent_count) * 100 ELSE 0 END) as avg_open_rate,
                AVG(CASE WHEN sent_count > 0 THEN (click_count / sent_count) * 100 ELSE 0 END) as avg_click_rate,
                AVG(CASE WHEN sent_count > 0 THEN (conversion_count / sent_count) * 100 ELSE 0 END) as avg_conversion_rate
             FROM campaign_analytics
             WHERE campaign_id = ? AND date >= ?",
            [$campaignId, $startDate]
        )->fetch();

        return $analytics ?: [
            'total_sent' => 0,
            'total_delivered' => 0,
            'total_opens' => 0,
            'total_clicks' => 0,
            'total_conversions' => 0,
            'total_bounces' => 0,
            'total_unsubscribes' => 0,
            'avg_open_rate' => 0,
            'avg_click_rate' => 0,
            'avg_conversion_rate' => 0
        ];
    }

    /**
     * Create message template
     */
    public function createMessageTemplate(array $templateData): array
    {
        $templateRecord = [
            'template_name' => $templateData['template_name'],
            'template_type' => $templateData['template_type'],
            'category' => $templateData['category'] ?? 'followup',
            'subject_template' => $templateData['subject_template'] ?? null,
            'content_template' => $templateData['content_template'],
            'variables' => json_encode($templateData['variables'] ?? []),
            'thumbnail' => $templateData['thumbnail'] ?? null,
            'is_default' => $templateData['is_default'] ?? 0,
            'is_active' => $templateData['is_active'] ?? 1,
            'created_by' => $templateData['created_by'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $templateId = $this->insertInto('message_templates', $templateRecord);

        return [
            'success' => true,
            'template_id' => $templateId,
            'message' => 'Message template created successfully'
        ];
    }

    /**
     * Get message templates
     */
    public function getMessageTemplates(string $type = null, string $category = null): array
    {
        $query = "SELECT * FROM message_templates WHERE is_active = 1";
        $params = [];

        if ($type) {
            $query .= " AND template_type = ?";
            $params[] = $type;
        }

        if ($category) {
            $query .= " AND category = ?";
            $params[] = $category;
        }

        $query .= " ORDER BY template_name ASC";

        return $this->query($query, $params)->fetchAll();
    }

    /**
     * Process automated triggers
     */
    public function processAutomatedTriggers(): array
    {
        $db = Database::getInstance();

        // Get active triggers
        $triggers = $this->query(
            "SELECT * FROM automated_triggers WHERE is_active = 1 ORDER BY priority DESC"
        )->fetchAll();

        $processed = 0;
        $actionsTaken = 0;

        foreach ($triggers as $trigger) {
            $triggerResult = $this->processTrigger($trigger);
            if ($triggerResult['processed']) {
                $processed++;
                $actionsTaken += $triggerResult['actions'];
            }
        }

        return [
            'success' => true,
            'triggers_processed' => $processed,
            'actions_taken' => $actionsTaken,
            'message' => "Processed {$processed} triggers, took {$actionsTaken} actions"
        ];
    }

    /**
     * Process individual trigger
     */
    private function processTrigger(array $trigger): array
    {
        $conditions = json_decode($trigger['conditions'], true);
        $actions = json_decode($trigger['actions'], true);

        // Check if trigger conditions are met
        $targets = $this->findTriggerTargets($trigger['trigger_type'], $conditions);

        $actionsTaken = 0;
        foreach ($targets as $target) {
            // Check cooldown
            if ($this->isOnCooldown($trigger, $target)) {
                continue;
            }

            // Execute actions
            $result = $this->executeTriggerActions($actions, $target, $trigger);
            if ($result) {
                $actionsTaken++;
            }
        }

        // Update trigger execution count
        $this->query(
            "UPDATE automated_triggers SET executed_count = executed_count + ?, updated_at = NOW() WHERE id = ?",
            [$actionsTaken, $trigger['id']]
        );

        return [
            'processed' => !empty($targets),
            'actions' => $actionsTaken
        ];
    }

    /**
     * Find targets for trigger
     */
    private function findTriggerTargets(string $triggerType, array $conditions): array
    {
        // This would implement logic to find leads/customers that match trigger conditions
        // For example, leads with score changes, behavior events, etc.
        return []; // Placeholder
    }

    /**
     * Check if target is on cooldown
     */
    private function isOnCooldown(array $trigger, array $target): bool
    {
        // Implement cooldown logic
        return false; // Placeholder
    }

    /**
     * Execute trigger actions
     */
    private function executeTriggerActions(array $actions, array $target, array $trigger): bool
    {
        $success = true;

        foreach ($actions as $actionType => $actionData) {
            switch ($actionType) {
                case 'send_email':
                    $success &= $this->sendTriggerEmail($actionData, $target);
                    break;
                case 'send_sms':
                    $success &= $this->sendTriggerSMS($actionData, $target);
                    break;
                case 'assign_to_agent':
                    $success &= $this->assignToAgent($target, $actionData);
                    break;
                case 'create_task':
                    $success &= $this->createTask($target, $actionData);
                    break;
            }
        }

        return $success;
    }

    // Helper methods (implementations would depend on your actual systems)
    private function getRecipientName(array $recipient): string { return 'Valued Customer'; }
    private function generateUnsubscribeLink(array $recipient): string { return '#'; }
    private function sendEmail(array $recipient, ?string $subject, string $content, array $message): array { return ['success' => true]; }
    private function sendSMS(array $recipient, string $content): array { return ['success' => true]; }
    private function sendWhatsApp(array $recipient, string $content): array { return ['success' => true]; }
    private function sendPush(array $recipient, ?string $subject, string $content): array { return ['success' => true]; }
    private function sendTriggerEmail(array $actionData, array $target): bool { return true; }
    private function sendTriggerSMS(array $actionData, array $target): bool { return true; }
    private function assignToAgent(array $target, array $actionData): bool { return true; }
    private function createTask(array $target, array $actionData): bool { return true; }
}
