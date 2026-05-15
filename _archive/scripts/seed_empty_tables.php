<?php
/**
 * Seed Test Data for Empty Tables
 * Run: php scripts/seed_empty_tables.php
 */

$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");

echo "=== SEEDING TEST DATA FOR EMPTY TABLES ===\n\n";

// ============================================
// 1. SEED: lead_assignment_history
// ============================================
echo "1. Seeding lead_assignment_history...\n";
$users = $pdo->query("SELECT id FROM users LIMIT 5")->fetchAll(PDO::FETCH_COLUMN);
$leads = $pdo->query("SELECT id FROM leads LIMIT 10")->fetchAll(PDO::FETCH_COLUMN);

if (!empty($users) && !empty($leads)) {
    $stmt = $pdo->prepare("INSERT INTO lead_assignment_history (lead_id, assigned_to, assigned_by, notes, assigned_at) VALUES (?, ?, ?, ?, ?)");
    foreach ($leads as $i => $leadId) {
        $assignedTo = $users[$i % count($users)];
        $assignedBy = $users[0];
        $notes = "Assigned based on location preference - Test Data";
        $assignedAt = date('Y-m-d H:i:s', strtotime("-$i days"));
        $stmt->execute([$leadId, $assignedTo, $assignedBy, $notes, $assignedAt]);
    }
    echo "   ✅ Seeded " . count($leads) . " assignment records\n";
}

// ============================================
// 2. SEED: lead_notes
// ============================================
echo "2. Seeding lead_notes...\n";
if (!empty($leads)) {
    $stmt = $pdo->prepare("INSERT INTO lead_notes (lead_id, content, is_private, created_by, created_at) VALUES (?, ?, ?, ?, ?)");
    $noteContents = [
        "Initial call - Customer interested in 3BHK in Gorakhpur area",
        "Follow-up call scheduled for next week",
        "Customer comparing with competitor project",
        "Budget discussion completed - max 50L",
        "Site visit scheduled for Saturday",
        "Customer needs home loan assistance",
        "Referral from existing customer",
        "Website inquiry - cold lead",
        "Met at property expo",
        "Social media ad response"
    ];
    foreach ($leads as $i => $leadId) {
        $content = $noteContents[$i % count($noteContents)];
        $isPrivate = $i % 3 == 0 ? 1 : 0;
        $createdBy = $users[0];
        $createdAt = date('Y-m-d H:i:s', strtotime("-$i days"));
        $stmt->execute([$leadId, $content, $isPrivate, $createdBy, $createdAt]);
    }
    echo "   ✅ Seeded " . count($leads) . " lead notes\n";
}

// ============================================
// 3. SEED: lead_files
// ============================================
echo "3. Seeding lead_files...\n";
if (!empty($leads)) {
    $stmt = $pdo->prepare("INSERT INTO lead_files (lead_id, original_name, file_path, file_type, file_size, uploaded_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $files = [
        ['Aadhar_Card.pdf', '/uploads/leads/', 'application/pdf', 245760],
        ['PAN_Card.jpg', '/uploads/leads/', 'image/jpeg', 102400],
        ['Salary_Slip.pdf', '/uploads/leads/', 'application/pdf', 153600],
        ['Property_Requirement.docx', '/uploads/leads/', 'application/docx', 51200],
    ];
    foreach ($leads as $i => $leadId) {
        $file = $files[$i % count($files)];
        $uploadedBy = $users[0];
        $createdAt = date('Y-m-d H:i:s', strtotime("-$i days"));
        $stmt->execute([$leadId, $file[0], $file[1] . $leadId . '_' . $file[0], $file[2], $file[3], $uploadedBy, $createdAt]);
    }
    echo "   ✅ Seeded " . count($leads) . " lead files\n";
}

// ============================================
// 4. SEED: lead_engagement_metrics (unique constraint: lead_id, metric_type, metric_date)
// ============================================
echo "4. Seeding lead_engagement_metrics...\n";
if (!empty($leads)) {
    $stmt = $pdo->prepare("INSERT IGNORE INTO lead_engagement_metrics (lead_id, metric_type, metric_value, metric_date, source) VALUES (?, ?, ?, ?, ?)");
    $metrics = ['page_views', 'inquiries', 'property_views', 'contact_attempts', 'meetings', 'offers'];
    $sources = ['website', 'whatsapp', 'phone', 'email', 'direct'];
    $today = date('Y-m-d');
    foreach ($leads as $i => $leadId) {
        // Only one metric per lead per day to avoid unique constraint
        $metricType = $metrics[$i % count($metrics)];
        $metricValue = rand(1, 20);
        $metricDate = date('Y-m-d', strtotime("-$i days", strtotime($today)));
        $source = $sources[$i % count($sources)];
        $stmt->execute([$leadId, $metricType, $metricValue, $metricDate, $source]);
    }
    echo "   ✅ Seeded engagement metrics\n";
}

// ============================================
// 5. SEED: lead_pipeline
// ============================================
echo "5. Seeding lead_pipeline...\n";
if (!empty($leads)) {
    $statuses = $pdo->query("SELECT id FROM lead_statuses")->fetchAll(PDO::FETCH_COLUMN);
    if (!empty($statuses)) {
        $stmt = $pdo->prepare("INSERT INTO lead_pipeline (lead_id, current_stage_id, assigned_to, assigned_at, entered_stage_at, priority, deal_value, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $priorities = ['low', 'normal', 'high', 'urgent'];
        foreach ($leads as $i => $leadId) {
            $stageId = $statuses[$i % count($statuses)];
            $assignedTo = $users[$i % count($users)];
            $assignedAt = date('Y-m-d H:i:s', strtotime("-$i days"));
            $enteredStageAt = date('Y-m-d H:i:s', strtotime("-" . ($i-1) . " days"));
            $priority = $priorities[$i % count($priorities)];
            $dealValue = rand(10, 100) * 100000;
            $isActive = 1;
            $stmt->execute([$leadId, $stageId, $assignedTo, $assignedAt, $enteredStageAt, $priority, $dealValue, $isActive]);
        }
        echo "   ✅ Seeded " . count($leads) . " pipeline records\n";
    }
}

// ============================================
// 6. SEED: lead_scoring_models
// ============================================
echo "6. Seeding lead_scoring_models...\n";
$models = [
    ['Budget Based Scoring', '{"budget_weight": 0.4, "location_weight": 0.3, "timeline_weight": 0.3}', '{"high_budget": 25, "medium_budget": 15, "low_budget": 5}', 70],
    ['Engagement Scoring', '{"page_views_weight": 0.3, "call_duration_weight": 0.4, "meeting_weight": 0.3}', '{"high_engagement": 30, "medium": 15, "low": 5}', 60],
    ['AI Hybrid Model', '{"ai_analysis_weight": 0.5, "demographics_weight": 0.25, "behavior_weight": 0.25}', '{"hot_lead": 40, "warm_lead": 20, "cold_lead": 5}', 85],
];
$stmt = $pdo->prepare("INSERT INTO lead_scoring_models (model_name, criteria, weights, threshold_score, created_at) VALUES (?, ?, ?, ?, NOW())");
foreach ($models as $model) {
    $stmt->execute($model);
}
echo "   ✅ Seeded " . count($models) . " scoring models\n";

// ============================================
// 7. SEED: lead_scores
// ============================================
echo "7. Seeding lead_scores...\n";
$scoringModels = $pdo->query("SELECT id FROM lead_scoring_models")->fetchAll(PDO::FETCH_COLUMN);
if (!empty($leads) && !empty($scoringModels)) {
    $stmt = $pdo->prepare("INSERT INTO lead_scores (lead_id, model_id, score, score_details, calculated_at) VALUES (?, ?, ?, ?, NOW())");
    foreach ($leads as $i => $leadId) {
        $modelId = $scoringModels[$i % count($scoringModels)];
        $score = rand(20, 95);
        $details = json_encode([
            'demographics' => rand(10, 30),
            'engagement' => rand(10, 30),
            'behavior' => rand(10, 30),
            'ai_analysis' => rand(10, 30)
        ]);
        $stmt->execute([$leadId, $modelId, $score, $details]);
    }
    echo "   ✅ Seeded " . count($leads) . " lead scores\n";
}

// ============================================
// 8. SEED: lead_scoring_history
// ============================================
echo "8. Seeding lead_scoring_history...\n";
if (!empty($leads)) {
    $scoringRules = $pdo->query("SELECT id FROM lead_scoring_rules")->fetchAll(PDO::FETCH_COLUMN);
    $stmt = $pdo->prepare("INSERT INTO lead_scoring_history (lead_id, rule_id, action, points_change, old_score, new_score, reason, applied_by, applied_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $actions = ['scored', 'manual_adjustment', 'decay'];
    foreach ($leads as $i => $leadId) {
        $ruleId = !empty($scoringRules) ? $scoringRules[$i % count($scoringRules)] : null;
        $action = $actions[$i % count($actions)];
        $pointsChange = rand(-10, 20);
        $oldScore = rand(30, 70);
        $newScore = $oldScore + $pointsChange;
        $reason = "Automated scoring - Test Data";
        $appliedBy = $users[0];
        $appliedAt = date('Y-m-d H:i:s', strtotime("-$i hours"));
        $stmt->execute([$leadId, $ruleId, $action, $pointsChange, $oldScore, $newScore, $reason, $appliedBy, $appliedAt]);
    }
    echo "   ✅ Seeded " . count($leads) . " scoring history records\n";
}

// ============================================
// 9. SEED: lead_deals
// ============================================
echo "9. Seeding lead_deals...\n";
if (!empty($leads)) {
    $properties = $pdo->query("SELECT id FROM properties LIMIT 10")->fetchAll(PDO::FETCH_COLUMN);
    $stmt = $pdo->prepare("INSERT INTO lead_deals (lead_id, deal_name, deal_value, stage, assigned_to, property_id, notes, probability, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stages = ['qualification', 'proposal', 'negotiation', 'closed_won'];
    foreach ($leads as $i => $leadId) {
        if ($i % 3 == 0) { // Only some leads have deals
            $dealName = "Deal #" . ($i + 1) . " - Property Purchase";
            $dealValue = rand(20, 150) * 100000;
            $stage = $stages[$i % count($stages)];
            $assignedTo = $users[$i % count($users)];
            $propertyId = !empty($properties) ? $properties[$i % count($properties)] : null;
            $notes = "Opportunity created from lead";
            $probability = rand(20, 90);
            $status = 'active';
            $stmt->execute([$leadId, $dealName, $dealValue, $stage, $assignedTo, $propertyId, $notes, $probability, $status]);
        }
    }
    echo "   ✅ Seeded deals from qualified leads\n";
}

// ============================================
// 10. SEED: lead_visits
// ============================================
echo "10. Seeding lead_visits...\n";
if (!empty($leads) && !empty($properties)) {
    $stmt = $pdo->prepare("INSERT INTO lead_visits (lead_id, property_id, visit_type, visit_date, duration_seconds, source, device_type, page_views, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $visitTypes = ['property_page', 'virtual_tour', 'video_call', 'site_visit'];
    $sources = ['google', 'facebook', 'direct', 'referral'];
    $devices = ['desktop', 'mobile', 'tablet'];
    foreach ($leads as $i => $leadId) {
        for ($j = 0; $j < rand(1, 4); $j++) {
            $propertyId = $properties[$j % count($properties)];
            $visitType = $visitTypes[rand(0, count($visitTypes) - 1)];
            $visitDate = date('Y-m-d H:i:s', strtotime("-$j days"));
            $duration = rand(30, 600);
            $source = $sources[rand(0, count($sources) - 1)];
            $device = $devices[rand(0, count($devices) - 1)];
            $pageViews = rand(1, 10);
            $stmt->execute([$leadId, $propertyId, $visitType, $visitDate, $duration, $source, $device, $pageViews, $visitDate]);
        }
    }
    echo "   ✅ Seeded property visits\n";
}

// ============================================
// 11. SEED: ai_context_memory
// ============================================
echo "11. Seeding ai_context_memory...\n";
$memories = [
    [1, 'preference', '3BHK flat in Gorakhpur', 'high'],
    [1, 'budget', '50 Lakhs', 'high'],
    [1, 'location', 'Near Railway Station', 'medium'],
    [2, 'preference', 'Residential Plot', 'high'],
    [2, 'budget', '30 Lakhs', 'high'],
    [3, 'interest', 'Commercial Property', 'high'],
    [3, 'budget', '1 Crore', 'critical'],
    [4, 'interest', '3BHK Apartment', 'medium'],
    [4, 'budget', '45 Lakhs', 'medium'],
];
$stmt = $pdo->prepare("INSERT INTO ai_context_memory (user_id, context_type, context_key, context_value, importance_level, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
foreach ($memories as $mem) {
    $stmt->execute($mem);
}
echo "   ✅ Seeded AI context memories\n";

// ============================================
// 12. SEED: ai_training_sessions
// ============================================
echo "12. Seeding ai_training_sessions...\n";
$sessions = [
    ['Property Query Model', 'Dataset: 5000 queries', 0.92, 'ready', date('Y-m-d H:i:s')],
    ['Lead Qualification', 'Dataset: 2000 leads', 0.78, 'training', null],
    ['Customer Service', 'Dataset: 10000 chats', 0, 'queued', null],
];
$stmt = $pdo->prepare("INSERT INTO ai_training_sessions (model_name, dataset_info, accuracy, status, completed_at) VALUES (?, ?, ?, ?, ?)");
foreach ($sessions as $sess) {
    $stmt->execute($sess);
}
echo "   ✅ Seeded AI training sessions\n";

// ============================================
// 13. SEED: ai_user_interactions
// ============================================
echo "13. Seeding ai_user_interactions...\n";
$interactions = [
    [1, 'session_001', 'property_inquiry', 'What is the price of 3BHK flat?', 'Price is 45 Lakhs for 1500 sqft', 'excellent'],
    [2, 'session_002', 'site_visit', 'I want to visit the property', 'Sure, let me schedule a visit', 'good'],
    [3, 'session_003', 'loan_inquiry', 'Do you provide home loan?', 'Yes, we have partnerships with major banks', 'average'],
    [1, 'session_001', 'followup', 'Is parking available?', 'Yes, covered parking available', 'excellent'],
    [4, 'session_004', 'general', 'Tell me about RERA approval', 'Project is RERA approved', 'good'],
];
$stmt = $pdo->prepare("INSERT INTO ai_user_interactions (user_id, session_id, interaction_type, user_input, ai_response, success_rating, interaction_timestamp) VALUES (?, ?, ?, ?, ?, ?, NOW())");
foreach ($interactions as $int) {
    $stmt->execute($int);
}
echo "   ✅ Seeded AI user interactions\n";

// ============================================
// 14. SEED: ai_learning_data
// ============================================
echo "14. Seeding ai_learning_data...\n";
$learningData = [
    [1, 1, 'page_view'],
    [1, 2, 'inquiry'],
    [2, 3, 'site_visit'],
    [3, 1, 'phone_call'],
    [4, 2, 'whatsapp_message'],
];
$stmt = $pdo->prepare("INSERT INTO ai_learning_data (user_id, property_id, action_type, created_at) VALUES (?, ?, ?, NOW())");
foreach ($learningData as $data) {
    $stmt->execute($data);
}
echo "   ✅ Seeded AI learning data\n";

// ============================================
// 15. SEED: kyc_details, kyc_documents, kyc_verification
// ============================================
echo "15. Seeding KYC tables...\n";
$customers = $pdo->query("SELECT id FROM customers")->fetchAll(PDO::FETCH_COLUMN);
if (!empty($customers)) {
    // kyc_details
    $stmt = $pdo->prepare("INSERT INTO kyc_details (user_id, pan_status, aadhaar_status, overall_status, created_at) VALUES (?, 'verified', 'verified', 'verified', NOW())");
    foreach ($customers as $custId) {
        $stmt->execute([$custId]);
    }
    
    // kyc_documents
    $stmt = $pdo->prepare("INSERT INTO kyc_documents (user_id, doc_type, file_path, verification_status, uploaded_at) VALUES (?, 'PAN', '/uploads/kyc/pan.pdf', 'verified', NOW())");
    foreach ($customers as $custId) {
        $stmt->execute([$custId]);
    }
    $stmt = $pdo->prepare("INSERT INTO kyc_documents (user_id, doc_type, file_path, verification_status, uploaded_at) VALUES (?, 'AADHAAR_FRONT', '/uploads/kyc/aadhar_front.pdf', 'verified', NOW())");
    foreach ($customers as $custId) {
        $stmt->execute([$custId]);
    }
    
    echo "   ✅ Seeded KYC data for " . count($customers) . " customers\n";
}

// ============================================
// 16. SEED: user_permissions
// ============================================
echo "16. Seeding user_permissions...\n";
$permissions = [
    ['users', 'create', 'Allow creating users'],
    ['users', 'read', 'Allow viewing users'],
    ['users', 'update', 'Allow updating users'],
    ['users', 'delete', 'Allow deleting users'],
    ['properties', 'create', 'Allow creating properties'],
    ['properties', 'read', 'Allow viewing properties'],
    ['leads', 'create', 'Allow creating leads'],
    ['leads', 'read', 'Allow viewing leads'],
    ['leads', 'update', 'Allow updating leads'],
    ['reports', 'read', 'Allow viewing reports'],
];
$stmt = $pdo->prepare("INSERT INTO user_permissions (resource, action, description) VALUES (?, ?, ?)");
foreach ($permissions as $perm) {
    $stmt->execute($perm);
}
echo "   ✅ Seeded " . count($permissions) . " permissions\n";

echo "\n=== SEEDING COMPLETE ===\n\n";

// Verify counts
$tables = ['lead_assignment_history', 'lead_notes', 'lead_files', 'lead_engagement_metrics', 
            'lead_pipeline', 'lead_scoring_models', 'lead_scores', 'lead_scoring_history',
            'lead_deals', 'lead_visits', 'ai_context_memory', 'ai_training_sessions',
            'ai_user_interactions', 'ai_learning_data', 'kyc_details', 'kyc_documents', 
            'kyc_verification', 'user_permissions'];

echo "VERIFICATION:\n";
foreach ($tables as $table) {
    try {
        $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        echo "  ✅ $table: $count rows\n";
    } catch (Exception $e) {
        echo "  ❌ $table: Error\n";
    }
}
