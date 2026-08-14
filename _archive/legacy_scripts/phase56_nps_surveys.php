<?php
require 'vendor/autoload.php';
require 'app/Core/Database.php';

$db = new App\Core\Database();
$pdo = $db->getPdo();

$tables = [
    'nps_surveys' => "CREATE TABLE IF NOT EXISTS nps_surveys (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(200) NOT NULL,
        description TEXT NULL,
        question_text VARCHAR(500) NOT NULL DEFAULT 'How likely are you to recommend us to a friend or colleague?',
        scale_min_label VARCHAR(100) NOT NULL DEFAULT 'Not at all likely',
        scale_max_label VARCHAR(100) NOT NULL DEFAULT 'Extremely likely',
        follow_up_question TEXT NULL,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        send_immediately TINYINT(1) NOT NULL DEFAULT 0,
        delay_days INT UNSIGNED NULL,
        delay_hours INT UNSIGNED NULL,
        trigger_event ENUM('property_viewed','inquiry_made','visit_completed','lead_converted','property_sold','manual') NOT NULL DEFAULT 'manual',
        created_by BIGINT UNSIGNED NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_active (is_active),
        INDEX idx_trigger (trigger_event)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'nps_responses' => "CREATE TABLE IF NOT EXISTS nps_responses (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        survey_id BIGINT UNSIGNED NOT NULL,
        user_id BIGINT UNSIGNED NULL,
        visitor_id BIGINT UNSIGNED NULL,
        score TINYINT UNSIGNED NOT NULL,
        category ENUM('detractor','passive','promoter') NOT NULL,
        follow_up_answer TEXT NULL,
        ip_address VARCHAR(64) NULL,
        user_agent VARCHAR(500) NULL,
        responded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_survey (survey_id),
        INDEX idx_user (user_id),
        INDEX idx_visitor (visitor_id),
        INDEX idx_category (category),
        INDEX idx_responded (responded_at),
        UNIQUE KEY uniq_survey_user (survey_id, user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'nps_schedule' => "CREATE TABLE IF NOT EXISTS nps_schedule (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        survey_id BIGINT UNSIGNED NOT NULL,
        user_id BIGINT UNSIGNED NOT NULL,
        scheduled_for DATETIME NOT NULL,
        sent_at DATETIME NULL,
        status ENUM('pending','sent','failed','cancelled') NOT NULL DEFAULT 'pending',
        attempt_count INT UNSIGNED NOT NULL DEFAULT 0,
        last_attempt_at DATETIME NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_survey (survey_id),
        INDEX idx_user (user_id),
        INDEX idx_status (status),
        INDEX idx_scheduled (scheduled_for)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

foreach ($tables as $name => $sql) {
    try {
        $pdo->exec($sql);
        echo "OK: $name\n";
    } catch (Exception $e) {
        echo "ERR: $name - " . $e->getMessage() . "\n";
    }
}

// Insert sample NPS survey if none exists
try {
    $count = $pdo->query("SELECT COUNT(*) FROM nps_surveys")->fetchColumn();
    if ($count == 0) {
        $pdo->prepare("INSERT INTO nps_surveys (title, description, question_text, scale_min_label, scale_max_label, follow_up_question, trigger_event, created_by) VALUES 
            ('Post-Visit NPS', 'Sent after property visit completion', 'How likely are you to recommend APS Dream Home to a friend or colleague?', 'Not at all likely', 'Extremely likely', 'What could we improve?', 'visit_completed', 1)")->execute();
        echo "Inserted sample NPS survey\n";
    }
} catch (Exception $e) { /* ignore */ }

echo "Phase 56: NPS Surveys tables created (3 tables)\n";?>