<?php
/**
 * Migration: Create AI Voice Infrastructure Tables
 * 
 * Creates all missing voice-related tables for the AI voice calling system:
 * - voice_provider_configs: Voice provider credentials and configuration
 * - voice_devices: Voice device registrations (SIP phones, softphones)
 * - voice_providers: Voice provider definitions
 * - voice_sms: SMS message records from voice providers
 * - voice_templates: Voice message templates (TTS scripts, IVR prompts)
 * - voice_webhooks: Voice webhook delivery logs
 * - voice_ivr: IVR flow definitions
 * - voice_ivr_flows: IVR flow versions
 * - voice_ivr_steps: Individual IVR steps
 * - voice_ivr_config: IVR configuration settings
 * - call_sessions: Call session tracking
 * - voice_alerts: Voice system alerts
 * - voice_notifications: Voice notification records
 * 
 * Idempotent: uses CREATE TABLE IF NOT EXISTS
 * 
 * Run via: php database/migrations/create_ai_voice_tables_s403.php
 */

require_once dirname(__DIR__, 2) . '/config/bootstrap_cli.php';
use App\Core\Database\Database;

try {
    $pdo = Database::getInstance()->getConnection();
    echo "✅ Database connected\n\n";
} catch (\Throwable $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

$created = 0;
$skipped = 0;

function tableExists(PDO $pdo, string $table): bool {
    $row = $pdo->query("SHOW TABLES LIKE '$table'")->fetch();
    return $row !== false;
}

function execMigration(PDO $pdo, string $name, string $sql): void {
    global $created, $skipped;
    if (tableExists($pdo, $name)) {
        echo "⏭️  SKIP: $name already exists\n";
        $skipped++;
        return;
    }
    try {
        $pdo->exec($sql);
        echo "✅ Created: $name\n";
        $created++;
    } catch (\Throwable $e) {
        echo "❌ ERROR creating $name: " . $e->getMessage() . "\n";
    }
}

// 1. voice_provider_configs
execMigration($pdo, 'voice_provider_configs', "
CREATE TABLE voice_provider_configs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    provider_name VARCHAR(50) NOT NULL UNIQUE,
    display_name VARCHAR(100) NOT NULL,
    api_key VARCHAR(255),
    api_secret VARCHAR(255),
    account_sid VARCHAR(255),
    auth_token VARCHAR(255),
    from_number VARCHAR(50),
    webhook_url VARCHAR(500),
    callback_url VARCHAR(500),
    is_active TINYINT(1) DEFAULT 1,
    is_default TINYINT(1) DEFAULT 0,
    priority INT DEFAULT 0,
    cost_per_minute DECIMAL(10,4) DEFAULT 0,
    tenant_id INT UNSIGNED DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_provider_active (provider_name, is_active),
    INDEX idx_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// 2. voice_devices
execMigration($pdo, 'voice_devices', "
CREATE TABLE voice_devices (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    device_id VARCHAR(100) NOT NULL UNIQUE,
    device_name VARCHAR(100),
    device_type ENUM('sip_phone','softphone','mobile','landline') DEFAULT 'softphone',
    sip_username VARCHAR(100),
    sip_password VARCHAR(255),
    sip_server VARCHAR(255),
    ip_address VARCHAR(45),
    mac_address VARCHAR(17),
    status ENUM('active','inactive','offline','maintenance') DEFAULT 'inactive',
    last_registered_at TIMESTAMP NULL,
    last_seen_at TIMESTAMP NULL,
    user_id INT UNSIGNED,
    tenant_id INT UNSIGNED DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_device_id (device_id),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// 3. voice_providers
execMigration($pdo, 'voice_providers', "
CREATE TABLE voice_providers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    provider_name VARCHAR(50) NOT NULL UNIQUE,
    display_name VARCHAR(100) NOT NULL,
    api_endpoint VARCHAR(500),
    auth_method ENUM('api_key','bearer_token','basic_auth','oauth2') DEFAULT 'api_key',
    supports_inbound TINYINT(1) DEFAULT 1,
    supports_outbound TINYINT(1) DEFAULT 1,
    supports_sms TINYINT(1) DEFAULT 0,
    supports_ivr TINYINT(1) DEFAULT 0,
    supports_recording TINYINT(1) DEFAULT 0,
    supports_transcription TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    config_schema JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_provider_name (provider_name),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// 4. voice_sms
execMigration($pdo, 'voice_sms', "
CREATE TABLE voice_sms (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    provider_name VARCHAR(50) NOT NULL,
    provider_message_id VARCHAR(255),
    from_number VARCHAR(50) NOT NULL,
    to_number VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('queued','sending','sent','delivered','failed','undelivered') DEFAULT 'queued',
    direction ENUM('inbound','outbound') DEFAULT 'outbound',
    cost DECIMAL(10,4) DEFAULT 0,
    currency VARCHAR(3) DEFAULT 'INR',
    sent_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    error_code VARCHAR(50),
    error_message TEXT,
    tenant_id INT UNSIGNED DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_provider (provider_name),
    INDEX idx_from (from_number),
    INDEX idx_to (to_number),
    INDEX idx_status (status),
    INDEX idx_direction (direction),
    INDEX idx_tenant (tenant_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// 5. voice_templates
execMigration($pdo, 'voice_templates', "
CREATE TABLE voice_templates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    template_name VARCHAR(100) NOT NULL,
    template_type ENUM('tts','ivr','welcome','goodbye','hold','transfer','error','custom') DEFAULT 'tts',
    language VARCHAR(10) DEFAULT 'en-IN',
    voice_gender ENUM('male','female','neutral') DEFAULT 'female',
    voice_name VARCHAR(100),
    text_content TEXT NOT NULL,
    ssml_content TEXT,
    audio_file_url VARCHAR(500),
    is_active TINYINT(1) DEFAULT 1,
    usage_count INT DEFAULT 0,
    tenant_id INT UNSIGNED DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_template_name_lang (template_name, language),
    INDEX idx_type (template_type),
    INDEX idx_language (language),
    INDEX idx_active (is_active),
    INDEX idx_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// 6. voice_webhooks
execMigration($pdo, 'voice_webhooks', "
CREATE TABLE voice_webhooks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    provider_name VARCHAR(50) NOT NULL,
    webhook_type ENUM('call_status','recording_ready','transcription_ready','sms_status','ivr_result','dtmf') NOT NULL,
    webhook_url VARCHAR(500) NOT NULL,
    secret_token VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    last_triggered_at TIMESTAMP NULL,
    trigger_count INT DEFAULT 0,
    tenant_id INT UNSIGNED DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_provider (provider_name),
    INDEX idx_type (webhook_type),
    INDEX idx_active (is_active),
    INDEX idx_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// 7. voice_ivr
execMigration($pdo, 'voice_ivr', "
CREATE TABLE voice_ivr (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ivr_name VARCHAR(100) NOT NULL,
    description TEXT,
    welcome_message TEXT,
    welcome_audio_url VARCHAR(500),
    timeout_seconds INT DEFAULT 30,
    max_retries INT DEFAULT 3,
    transfer_number VARCHAR(50),
    transfer_extension VARCHAR(20),
    business_hours_only TINYINT(1) DEFAULT 0,
    business_start_time TIME DEFAULT '09:00:00',
    business_end_time TIME DEFAULT '18:00:00',
    business_days VARCHAR(20) DEFAULT '1,2,3,4,5',
    is_active TINYINT(1) DEFAULT 1,
    is_default TINYINT(1) DEFAULT 0,
    tenant_id INT UNSIGNED DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_ivr_name (ivr_name),
    INDEX idx_active (is_active),
    INDEX idx_default (is_default),
    INDEX idx_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// 8. voice_ivr_flows
execMigration($pdo, 'voice_ivr_flows', "
CREATE TABLE voice_ivr_flows (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ivr_id INT UNSIGNED NOT NULL,
    flow_name VARCHAR(100) NOT NULL,
    description TEXT,
    version INT DEFAULT 1,
    is_active TINYINT(1) DEFAULT 1,
    entry_step_id INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ivr_id) REFERENCES voice_ivr(id) ON DELETE CASCADE,
    INDEX idx_ivr_id (ivr_id),
    INDEX idx_flow_name (flow_name),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// 9. voice_ivr_steps
execMigration($pdo, 'voice_ivr_steps', "
CREATE TABLE voice_ivr_steps (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    flow_id INT UNSIGNED NOT NULL,
    step_id VARCHAR(50) NOT NULL,
    step_name VARCHAR(100),
    step_type ENUM('play','gather','menu','transfer','hangup','condition','variable') DEFAULT 'play',
    prompt_text TEXT,
    prompt_audio_url VARCHAR(500),
    input_type ENUM('none','dtmf','speech') DEFAULT 'dtmf',
    max_digits INT DEFAULT 1,
    timeout_seconds INT DEFAULT 5,
    next_step_id INT UNSIGNED,
    conditions JSON,
    transfer_number VARCHAR(50),
    transfer_extension VARCHAR(20),
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (flow_id) REFERENCES voice_ivr_flows(id) ON DELETE CASCADE,
    FOREIGN KEY (next_step_id) REFERENCES voice_ivr_steps(id) ON DELETE SET NULL,
    INDEX idx_flow_id (flow_id),
    INDEX idx_step_id (step_id),
    INDEX idx_step_type (step_type),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// 10. voice_ivr_config
execMigration($pdo, 'voice_ivr_config', "
CREATE TABLE voice_ivr_config (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(100) NOT NULL UNIQUE,
    config_value TEXT,
    config_type ENUM('string','int','bool','json') DEFAULT 'string',
    description VARCHAR(255),
    tenant_id INT UNSIGNED DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_config_key (config_key),
    INDEX idx_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// 11. call_sessions
execMigration($pdo, 'call_sessions', "
CREATE TABLE call_sessions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    call_sid VARCHAR(100) NOT NULL UNIQUE,
    call_token VARCHAR(255),
    from_number VARCHAR(50),
    to_number VARCHAR(50),
    direction ENUM('inbound','outbound') DEFAULT 'outbound',
    status ENUM('initiated','ringing','answered','completed','failed','busy','no-answer','cancelled') DEFAULT 'initiated',
    start_time TIMESTAMP NULL,
    answer_time TIMESTAMP NULL,
    end_time TIMESTAMP NULL,
    duration_seconds INT DEFAULT 0,
    billable_seconds INT DEFAULT 0,
    cost DECIMAL(10,4) DEFAULT 0,
    currency VARCHAR(3) DEFAULT 'INR',
    provider_name VARCHAR(50),
    provider_call_id VARCHAR(255),
    recording_url VARCHAR(500),
    recording_duration INT DEFAULT 0,
    transcription_text TEXT,
    transcription_confidence DECIMAL(5,4),
    sentiment ENUM('positive','neutral','negative') DEFAULT 'neutral',
    sentiment_score DECIMAL(5,4),
    intent VARCHAR(100),
    lead_id INT UNSIGNED,
    extracted_lead_id INT UNSIGNED,
    agent_id INT UNSIGNED,
    notes TEXT,
    tenant_id INT UNSIGNED DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_call_sid (call_sid),
    INDEX idx_from (from_number),
    INDEX idx_to (to_number),
    INDEX idx_direction (direction),
    INDEX idx_status (status),
    INDEX idx_provider (provider_name),
    INDEX idx_lead_id (lead_id),
    INDEX idx_agent_id (agent_id),
    INDEX idx_tenant (tenant_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// 12. voice_alerts
execMigration($pdo, 'voice_alerts', "
CREATE TABLE voice_alerts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    alert_type ENUM('call_failed','system_error','low_balance','provider_down','spike_in_failures','quality_issue') NOT NULL,
    severity ENUM('info','warning','error','critical') DEFAULT 'warning',
    title VARCHAR(255) NOT NULL,
    message TEXT,
    provider_name VARCHAR(50),
    related_id INT UNSIGNED,
    related_type VARCHAR(50),
    is_resolved TINYINT(1) DEFAULT 0,
    resolved_at TIMESTAMP NULL,
    resolved_by INT UNSIGNED,
    tenant_id INT UNSIGNED DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (alert_type),
    INDEX idx_severity (severity),
    INDEX idx_resolved (is_resolved),
    INDEX idx_provider (provider_name),
    INDEX idx_tenant (tenant_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// 13. voice_notifications
execMigration($pdo, 'voice_notifications', "
CREATE TABLE voice_notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED,
    title VARCHAR(255) NOT NULL,
    message TEXT,
    notification_type ENUM('call_reminder','follow_up','lead_assigned','system_alert','promotional') DEFAULT 'system_alert',
    phone_number VARCHAR(50),
    call_sid VARCHAR(100),
    status ENUM('pending','sent','delivered','failed','read') DEFAULT 'pending',
    sent_at TIMESTAMP NULL,
    read_at TIMESTAMP NULL,
    tenant_id INT UNSIGNED DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_type (notification_type),
    INDEX idx_status (status),
    INDEX idx_call_sid (call_sid),
    INDEX idx_tenant (tenant_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// 14. ai_feedback
execMigration($pdo, 'ai_feedback', "
CREATE TABLE ai_feedback (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED,
    session_id VARCHAR(100),
    message_id VARCHAR(100),
    rating INT CHECK (rating >= 1 AND rating <= 5),
    feedback_text TEXT,
    category ENUM('accuracy','relevance','speed','quality','other') DEFAULT 'quality',
    is_helpful TINYINT(1) DEFAULT 1,
    tenant_id INT UNSIGNED DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_session_id (session_id),
    INDEX idx_message_id (message_id),
    INDEX idx_rating (rating),
    INDEX idx_category (category),
    INDEX idx_tenant (tenant_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// 15. ai_config
execMigration($pdo, 'ai_config', "
CREATE TABLE ai_config (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(100) NOT NULL UNIQUE,
    config_value TEXT,
    config_type ENUM('string','int','float','bool','json') DEFAULT 'string',
    description VARCHAR(255),
    is_editable TINYINT(1) DEFAULT 1,
    tenant_id INT UNSIGNED DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_config_key (config_key),
    INDEX idx_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// 16. ai_chatbot_config
execMigration($pdo, 'ai_chatbot_config', "
CREATE TABLE ai_chatbot_config (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(100) NOT NULL UNIQUE,
    config_value TEXT,
    config_type ENUM('string','int','float','bool','json') DEFAULT 'string',
    description VARCHAR(255),
    tenant_id INT UNSIGNED DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_config_key (config_key),
    INDEX idx_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// 17. chatbot_training_data
execMigration($pdo, 'chatbot_training_data', "
CREATE TABLE chatbot_training_data (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    intent_name VARCHAR(100) NOT NULL,
    training_phrase TEXT NOT NULL,
    response_text TEXT,
    language VARCHAR(10) DEFAULT 'en',
    is_active TINYINT(1) DEFAULT 1,
    usage_count INT DEFAULT 0,
    tenant_id INT UNSIGNED DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_intent_name (intent_name),
    INDEX idx_language (language),
    INDEX idx_active (is_active),
    INDEX idx_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// 18. ai_prediction_accuracy
execMigration($pdo, 'ai_prediction_accuracy', "
CREATE TABLE ai_prediction_accuracy (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    model_name VARCHAR(100) NOT NULL,
    prediction_type VARCHAR(50) NOT NULL,
    predicted_value TEXT,
    actual_value TEXT,
    accuracy DECIMAL(5,4),
    confidence DECIMAL(5,4),
    tenant_id INT UNSIGNED DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_model_name (model_name),
    INDEX idx_prediction_type (prediction_type),
    INDEX idx_tenant (tenant_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// 19. ai_predictions
execMigration($pdo, 'ai_predictions', "
CREATE TABLE ai_predictions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    prediction_type VARCHAR(50) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT UNSIGNED NOT NULL,
    predicted_value TEXT,
    confidence DECIMAL(5,4),
    actual_value TEXT,
    actualized_at TIMESTAMP NULL,
    is_accurate TINYINT(1),
    tenant_id INT UNSIGNED DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_prediction_type (prediction_type),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_tenant (tenant_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// 20. ai_scoring_models
execMigration($pdo, 'ai_scoring_models', "
CREATE TABLE ai_scoring_models (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    model_name VARCHAR(100) NOT NULL,
    model_version VARCHAR(50) DEFAULT '1.0',
    model_type VARCHAR(50) DEFAULT 'lead_score',
    accuracy DECIMAL(5,4),
    precision_score DECIMAL(5,4),
    recall DECIMAL(5,4),
    f1_score DECIMAL(5,4),
    training_samples INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    is_default TINYINT(1) DEFAULT 0,
    features JSON,
    weights JSON,
    tenant_id INT UNSIGNED DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_model_name (model_name),
    INDEX idx_model_type (model_type),
    INDEX idx_active (is_active),
    INDEX idx_default (is_default),
    INDEX idx_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// 21. ai_customer_segments
execMigration($pdo, 'ai_customer_segments', "
CREATE TABLE ai_customer_segments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    segment_name VARCHAR(100) NOT NULL,
    segment_description TEXT,
    segment_rules JSON,
    customer_count INT DEFAULT 0,
    avg_conversion_rate DECIMAL(5,4),
    avg_revenue_per_customer DECIMAL(10,2),
    is_active TINYINT(1) DEFAULT 1,
    tenant_id INT UNSIGNED DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_segment_name (segment_name),
    INDEX idx_active (is_active),
    INDEX idx_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// 21. ai_config
execMigration($pdo, 'ai_config', "
CREATE TABLE ai_config (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(100) NOT NULL UNIQUE,
    config_value TEXT,
    config_type ENUM('string','int','float','bool','json') DEFAULT 'string',
    description VARCHAR(255),
    is_editable TINYINT(1) DEFAULT 1,
    tenant_id INT UNSIGNED DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_config_key (config_key),
    INDEX idx_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "\n=== Migration Summary ===\n";
echo "Created: $created tables\n";
echo "Skipped: $skipped tables (already exist)\n";
echo "\n✅ AI Voice Infrastructure migration complete\n";