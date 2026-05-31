<?php

/**
 * Step 1: Implement Critical AI Features
 * Fill empty AI tables with proper data and functionality
 */

$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");

echo "=== STEP 1: IMPLEMENT CRITICAL AI FEATURES ===\n\n";

try {
    // ============================================
    // 1. Implement ai_context_memory
    // ============================================
    echo "1. Implementing ai_context_memory...\n";

    // Check if table has data
    $contextCount = $pdo->query("SELECT COUNT(*) FROM ai_context_memory")->fetchColumn();

    if ($contextCount == 0) {
        // Seed context memory from existing conversations
        $insertContext = $pdo->prepare("INSERT INTO ai_context_memory (user_id, context_type, context_key, context_value, importance_level, created_at) 
VALUES (:user_id, 'conversation', :context_key, :context_value, 'medium', NOW())");

        // Get existing conversations
        $conversations = $pdo->query("SELECT id, user_id, message, response FROM ai_conversations WHERE message IS NOT NULL LIMIT 50")->fetchAll();

        foreach ($conversations as $conv) {
            // Skip conversations with NULL user_id due to foreign key constraint
            if ($conv['user_id'] === null) {
                continue;
            }

            // Use simple text instead of JSON to avoid constraint issues
            $contextValue = "Session {$conv['id']}: " . substr($conv['message'], 0, 200);

            $insertContext->execute([
                ':user_id' => $conv['user_id'],
                ':context_key' => 'conversation_session_' . $conv['id'],
                ':context_value' => $contextValue
            ]);
        }

        // Add default preferences for all users
        $users = $pdo->query("SELECT id FROM users LIMIT 50")->fetchAll();
        foreach ($users as $user) {
            $insertContext->execute([
                ':user_id' => $user['id'],
                ':context_key' => 'preferred_language',
                ':context_value' => 'en'
            ]);

            $insertContext->execute([
                ':user_id' => $user['id'],
                ':context_key' => 'notification_preference',
                ':context_value' => 'email'
            ]);
        }

        echo "   ✓ ai_context_memory seeded with " . ($pdo->query("SELECT COUNT(*) FROM ai_context_memory")->fetchColumn()) . " records\n";
    } else {
        echo "   - ai_context_memory already has $contextCount records\n";
    }

    // ============================================
    // 2. Implement ai_user_preferences
    // ============================================
    echo "\n2. Implementing ai_user_preferences...\n";

    $prefCount = $pdo->query("SELECT COUNT(*) FROM ai_user_preferences")->fetchColumn();

    if ($prefCount == 0) {
        // Insert default preferences for all users
        $insertPref = $pdo->prepare("INSERT INTO ai_user_preferences (user_id, preference_category, preference_key, preference_value, usage_count, last_updated) 
VALUES (:user_id, :category, :key, :value, 0, NOW()) 
ON DUPLICATE KEY UPDATE usage_count = usage_count + 1");

        $users = $pdo->query("SELECT id FROM users")->fetchAll();

        foreach ($users as $user) {
            // Language preference
            $insertPref->execute([
                ':user_id' => $user['id'],
                ':category' => 'general',
                ':key' => 'language',
                ':value' => 'en'
            ]);

            // Theme preference
            $insertPref->execute([
                ':user_id' => $user['id'],
                ':category' => 'ui',
                ':key' => 'theme',
                ':value' => 'light'
            ]);

            // Notification preference
            $insertPref->execute([
                ':user_id' => $user['id'],
                ':category' => 'notifications',
                ':key' => 'email_enabled',
                ':value' => '1'
            ]);

            // AI response style
            $insertPref->execute([
                ':user_id' => $user['id'],
                ':category' => 'ai',
                ':key' => 'response_style',
                ':value' => 'professional'
            ]);
        }

        echo "   ✓ ai_user_preferences seeded with " . ($pdo->query("SELECT COUNT(*) FROM ai_user_preferences")->fetchColumn()) . " records\n";
    } else {
        echo "   - ai_user_preferences already has $prefCount records\n";
    }

    // ============================================
    // 3. Implement ai_user_profiles
    // ============================================
    echo "\n3. Implementing ai_user_profiles...\n";

    $profileCount = $pdo->query("SELECT COUNT(*) FROM ai_user_profiles")->fetchColumn();

    if ($profileCount == 0) {
        // Create profiles for all users
        $insertProfile = $pdo->prepare("INSERT INTO ai_user_profiles (user_id, total_interactions, last_interaction, created_at, updated_at) 
VALUES (:user_id, 0, NOW(), NOW(), NOW()) 
ON DUPLICATE KEY UPDATE last_interaction = NOW()");

        $users = $pdo->query("SELECT id FROM users")->fetchAll();

        foreach ($users as $user) {
            $insertProfile->execute([':user_id' => $user['id']]);
        }

        // Update with actual interaction counts if available
        $pdo->exec("UPDATE ai_user_profiles up 
SET total_interactions = (
    SELECT COUNT(*) FROM ai_conversations WHERE user_id = up.user_id
)");

        echo "   ✓ ai_user_profiles seeded with " . ($pdo->query("SELECT COUNT(*) FROM ai_user_profiles")->fetchColumn()) . " records\n";
    } else {
        echo "   - ai_user_profiles already has $profileCount records\n";
    }

    // ============================================
    // 4. Implement ai_agent_state
    // ============================================
    echo "\n4. Implementing ai_agent_state...\n";

    $agentStateCount = $pdo->query("SELECT COUNT(*) FROM ai_agent_state")->fetchColumn();

    if ($agentStateCount == 0) {
        // Initialize agent state for active users
        $insertAgentState = $pdo->prepare("INSERT INTO ai_agent_state (user_id, current_mode, context_data, last_action_report, updated_at) 
VALUES (:user_id, 'assistant', '{}', 'Initialized', NOW())");

        // Get users who have interacted with AI
        $aiUsers = $pdo->query("SELECT DISTINCT user_id FROM ai_conversations WHERE user_id IS NOT NULL LIMIT 20")->fetchAll();

        foreach ($aiUsers as $user) {
            $insertAgentState->execute([':user_id' => $user['user_id']]);
        }

        echo "   ✓ ai_agent_state seeded with " . ($pdo->query("SELECT COUNT(*) FROM ai_agent_state")->fetchColumn()) . " records\n";
    } else {
        echo "   - ai_agent_state already has $agentStateCount records\n";
    }

    // ============================================
    // 5. Implement ai_workflow_patterns
    // ============================================
    echo "\n5. Implementing ai_workflow_patterns...\n";

    $patternCount = $pdo->query("SELECT COUNT(*) FROM ai_workflow_patterns")->fetchColumn();

    if ($patternCount == 0) {
        // Add common workflow patterns
        $patterns = [
            [
                'pattern_name' => 'property_inquiry_flow',
                'pattern_category' => 'sales',
                'trigger_conditions' => '{"event": "property_view", "action": "inquiry"}',
                'action_sequence' => '[{"step": "send_welcome", "next": "schedule_call"}, {"step": "schedule_call", "next": "follow_up"}]',
                'frequency_count' => 0
            ],
            [
                'pattern_name' => 'lead_nurturing_sequence',
                'pattern_category' => 'crm',
                'trigger_conditions' => '{"event": "lead_created", "status": "new"}',
                'action_sequence' => '[{"step": "send_intro", "delay": "1h"}, {"step": "send_properties", "delay": "24h"}, {"step": "schedule_visit", "delay": "48h"}]',
                'frequency_count' => 0
            ],
            [
                'pattern_name' => 'booking_confirmation_flow',
                'pattern_category' => 'booking',
                'trigger_conditions' => '{"event": "booking_created"}',
                'action_sequence' => '[{"step": "send_confirmation", "next": "payment_reminder"}, {"step": "payment_reminder", "delay": "24h"}]',
                'frequency_count' => 0
            ]
        ];

        $insertPattern = $pdo->prepare("INSERT INTO ai_workflow_patterns (pattern_name, pattern_category, trigger_conditions, action_sequence, frequency_count, last_used) 
VALUES (?, ?, ?, ?, ?, NOW())");

        foreach ($patterns as $pattern) {
            $insertPattern->execute([
                $pattern['pattern_name'],
                $pattern['pattern_category'],
                $pattern['trigger_conditions'],
                $pattern['action_sequence'],
                $pattern['frequency_count']
            ]);
        }

        echo "   ✓ ai_workflow_patterns seeded with " . ($pdo->query("SELECT COUNT(*) FROM ai_workflow_patterns")->fetchColumn()) . " records\n";
    } else {
        echo "   - ai_workflow_patterns already has $patternCount records\n";
    }

    echo "\n=== AI FEATURES IMPLEMENTATION COMPLETE ===\n";
    echo "Summary:\n";
    echo "- ai_context_memory: " . $pdo->query("SELECT COUNT(*) FROM ai_context_memory")->fetchColumn() . " records\n";
    echo "- ai_user_preferences: " . $pdo->query("SELECT COUNT(*) FROM ai_user_preferences")->fetchColumn() . " records\n";
    echo "- ai_user_profiles: " . $pdo->query("SELECT COUNT(*) FROM ai_user_profiles")->fetchColumn() . " records\n";
    echo "- ai_agent_state: " . $pdo->query("SELECT COUNT(*) FROM ai_agent_state")->fetchColumn() . " records\n";
    echo "- ai_workflow_patterns: " . $pdo->query("SELECT COUNT(*) FROM ai_workflow_patterns")->fetchColumn() . " records\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
