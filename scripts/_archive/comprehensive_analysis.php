<?php
/**
 * Comprehensive Database Analysis - Understanding Purpose & Consolidation
 */

// Connect to database
$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");

echo "=== COMPREHENSIVE DATABASE ANALYSIS ===\n";
echo "Understanding Purpose, Consolidation & Smart Features\n\n";

// ==========================================
// SECTION 1: USER/CUSTOMER CONSOLIDATION
// ==========================================
echo "=== SECTION 1: USER ECOSYSTEM ===\n";
echo str_repeat("=", 60) . "\n\n";

// Check all tables that store user info
$userRelatedTables = [
    'users', 'customers', 'admin_users', 
    'agents', 'associates', 'employees',
    'customer_profiles', 'customer_preferences',
    'user_roles', 'user_permissions',
    'kyc_details', 'kyc_documents', 'kyc_verification'
];

echo "USER-RELATED TABLES FOUND:\n";
foreach ($userRelatedTables as $table) {
    try {
        $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        $cols = $pdo->query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_COLUMN);
        echo "- $table: " . count($cols) . " cols, $count rows\n";
    } catch (Exception $e) {
        echo "- $table: TABLE NOT FOUND or BROKEN\n";
    }
}

// Check what unique data each user table has
echo "\nUNIQUE DATA PER USER TABLE:\n";
$userTables = ['users', 'customers', 'admin_users', 'agents', 'associates', 'employees'];
foreach ($userTables as $table) {
    try {
        $cols = $pdo->query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_ASSOC);
        $uniqueCols = [];
        foreach ($cols as $col) {
            // Check if this column is unique to this table
            $isCommon = in_array($col['Field'], ['id', 'email', 'phone', 'password', 'created_at', 'updated_at', 'status']);
            if (!$isCommon) {
                $uniqueCols[] = $col['Field'];
            }
        }
        echo "\n$table UNIQUE COLUMNS:\n";
        foreach ($uniqueCols as $uc) {
            echo "  + $uc\n";
        }
    } catch (Exception $e) {}
}

// ==========================================
// SECTION 2: LEAD ECOSYSTEM - FULL FEATURES
// ==========================================
echo "\n\n=== SECTION 2: LEAD CRM ECOSYSTEM ===\n";
echo str_repeat("=", 60) . "\n\n";

// Get all lead tables
$allTables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
$leadTables = array_filter($allTables, fn($t) => preg_match('/^lead/i', $t));

echo "LEAD TABLES AND THEIR PURPOSES:\n\n";

$leadFeatures = [
    'leads' => 'Main lead storage - name, email, phone, status, source',
    'lead_activities' => 'Activity log - calls, emails, meetings, notes',
    'lead_assignment_history' => 'Track who was assigned to which lead and when',
    'lead_deals' => 'Deals/opportunities created from leads',
    'lead_engagement_metrics' => 'Track engagement - page views, clicks, time spent',
    'lead_files' => 'Store documents uploaded for a lead',
    'lead_notes' => 'Internal notes about the lead',
    'lead_pipeline' => 'Sales pipeline stages and movement',
    'lead_scores' => 'AI-calculated scores for each lead',
    'lead_scoring' => 'Scoring criteria and calculations',
    'lead_scoring_history' => 'History of score changes',
    'lead_scoring_models' => 'ML models for lead scoring',
    'lead_sources' => 'Source types - Website, Referral, Campaign, etc',
    'lead_statuses' => 'Status definitions - New, Contacted, Qualified, etc',
    'lead_tags' => 'Tags - Hot, Cold, Follow-up, VIP, etc',
    'lead_tag_mapping' => 'Many-to-many mapping of leads to tags',
    'lead_visits' => 'Track website visits by leads',
];

foreach ($leadTables as $table) {
    $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
    $purpose = $leadFeatures[$table] ?? 'Purpose not documented';
    $status = $count > 0 ? "✅" : "❌";
    echo "$status $table ($count rows)\n";
    echo "   Purpose: $purpose\n";
    if ($count == 0) {
        echo "   ⚠️  NEEDS: Test data seed + Feature implementation\n";
    }
    echo "\n";
}

// ==========================================
// SECTION 3: AI FEATURE ECOSYSTEM
// ==========================================
echo "\n=== SECTION 3: AI ECOSYSTEM ===\n";
echo str_repeat("=", 60) . "\n\n";

$aiTables = array_filter($allTables, fn($t) => preg_match('/^ai_/i', $t));
$aiFeatures = [
    'ai_agents' => 'AI Agent configurations',
    'ai_agent_personality' => 'Agent personalities and traits',
    'ai_agent_state' => 'Current state of AI agents',
    'ai_audit_log' => 'All AI actions logged',
    'ai_bot_performance' => 'Bot performance metrics',
    'ai_chat_history' => 'Chat conversations',
    'ai_chatbot_config' => 'Chatbot settings',
    'ai_config' => 'Global AI configuration',
    'ai_configuration' => 'Detailed AI settings',
    'ai_context_memory' => 'Long-term memory for AI',
    'ai_conversation_states' => 'State of ongoing conversations',
    'ai_ecosystem_tools' => 'Tools available to AI',
    'ai_implementation_guides' => 'Guides for AI features',
    'ai_interaction_logs' => 'All AI interactions',
    'ai_knowledge_base' => 'Knowledge base for AI',
    'ai_knowledge_graph' => 'Knowledge relationships',
    'ai_learning_progress' => 'AI learning metrics',
    'ai_recommendations' => 'AI recommendations',
    'ai_settings' => 'AI settings',
    'ai_tools_directory' => 'Available AI tools',
    'ai_user_suggestions' => 'User-specific AI suggestions',
    'ai_workflows' => 'AI workflow definitions',
];

echo "AI TABLES AND FEATURES:\n\n";
foreach ($aiTables as $table) {
    $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
    $purpose = $aiFeatures[$table] ?? 'AI feature';
    $status = $count > 0 ? "✅" : "❌";
    echo "$status $table ($count rows) - $purpose\n";
}

// ==========================================
// SECTION 4: CONSOLIDATION OPPORTUNITIES
// ==========================================
echo "\n\n=== SECTION 4: CONSOLIDATION OPPORTUNITIES ===\n";
echo str_repeat("=", 60) . "\n\n";

// Check for duplicate purposes
echo "1. USER CONSOLIDATION:\n";
echo "   CURRENT: users, customers, admin_users, agents, associates, employees\n";
echo "   PROPOSED: Create unified 'user_profiles' table OR enhance 'users' table\n";
echo "   \n";

echo "2. LEAD CONSOLIDATION:\n";
echo "   CURRENT: 17 lead tables\n";
echo "   PROPOSED: Keep all (they serve different purposes in CRM)\n";
echo "   - leads: Main data\n";
echo "   - lead_activities: Activity log (audit trail)\n";
echo "   - lead_*: Supporting features\n";
echo "   \n";

echo "3. KYC/VERIFICATION CONSOLIDATION:\n";
echo "   CURRENT: kyc_details, kyc_documents, kyc_verification\n";
echo "   PROPOSED: Add to users/customers table OR create user_verifications\n";
echo "   \n";

echo "4. COMMON TABLES TO CREATE:\n";
echo "   - addresses: Universal address storage (billing, shipping, permanent)\n";
echo "   - bank_accounts: Universal bank details\n";
echo "   - documents: Universal document storage\n";
echo "   - notifications: Unified notification system\n";
echo "   - activity_logs: Universal activity tracking\n";

// ==========================================
// SECTION 5: SMART FEATURES TO IMPLEMENT
// ==========================================
echo "\n\n=== SECTION 5: SMART FEATURES TO IMPLEMENT ===\n";
echo str_repeat("=", 60) . "\n\n";

echo "LEAD SCORING SYSTEM:\n";
echo "  1. lead_scoring_models - ML models for scoring\n";
echo "  2. lead_scores - Calculate scores based on:\n";
echo "     - Demographics (budget, location preference)\n";
echo "     - Behavior (website visits, email opens, form fills)\n";
echo "     - Engagement (response time, meeting attendance)\n";
echo "     - AI Analysis (chat conversations, property interest)\n";
echo "  3. lead_scoring_history - Track score changes over time\n";
echo "  4. Auto-assign hot leads to sales team\n\n";

echo "AUTOMATION TRIGGERS:\n";
echo "  1. New lead → Auto-assign based on location/preference\n";
echo "  2. Lead not contacted in 24hrs → Alert manager\n";
echo "  3. Lead score > 80 → Auto-escalate\n";
echo "  4. Budget > 1cr → Auto-tag as VIP\n";
echo "  5. Multiple site visits → Increase score\n\n";

echo "AI AGENT FEATURES:\n";
echo "  1. ai_agents - Virtual sales assistants\n";
echo "  2. ai_knowledge_base - Property info, FAQs\n";
echo "  3. ai_workflows - Automated responses\n";
echo "  4. ai_context_memory - Remember conversation context\n";
echo "  5. Auto-followup via WhatsApp/Email\n";
