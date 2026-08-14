<?php
/**
 * Phase 53: Lead Nurturing Drip Campaigns
 * Automated email sequences based on triggers
 */
$db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$db->exec("DROP TABLE IF EXISTS drip_campaigns");
$db->exec("CREATE TABLE drip_campaigns (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    trigger_event ENUM('new_lead','property_inquiry','inactive_lead','post_visit','post_purchase','manual') NOT NULL DEFAULT 'new_lead',
    status ENUM('draft','active','paused','archived') NOT NULL DEFAULT 'draft',
    target_filter JSON NULL,
    total_enrolled INT(11) NOT NULL DEFAULT 0,
    total_completed INT(11) NOT NULL DEFAULT 0,
    emails_sent INT(11) NOT NULL DEFAULT 0,
    created_by BIGINT(20) UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status, trigger_event),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "OK drip_campaigns table created\n";

$db->exec("DROP TABLE IF EXISTS drip_emails");
$db->exec("CREATE TABLE drip_emails (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    campaign_id INT(11) NOT NULL,
    sequence_order INT(11) NOT NULL,
    delay_days INT(11) NOT NULL DEFAULT 0,
    delay_hours INT(11) NOT NULL DEFAULT 0,
    subject VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    channel ENUM('email','sms','whatsapp') NOT NULL DEFAULT 'email',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_campaign (campaign_id, sequence_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "OK drip_emails table created\n";

$db->exec("DROP TABLE IF EXISTS drip_enrollments");
$db->exec("CREATE TABLE drip_enrollments (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    campaign_id INT(11) NOT NULL,
    lead_id INT(11) NULL,
    user_id BIGINT(20) UNSIGNED NULL,
    email VARCHAR(150) NOT NULL,
    name VARCHAR(150) NULL,
    enrolled_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    next_send_at TIMESTAMP NULL,
    current_step INT(11) NOT NULL DEFAULT 0,
    status ENUM('active','paused','completed','unsubscribed','bounced') NOT NULL DEFAULT 'active',
    completed_at TIMESTAMP NULL,
    last_sent_at TIMESTAMP NULL,
    total_sent INT(11) NOT NULL DEFAULT 0,
    INDEX idx_campaign (campaign_id, status),
    INDEX idx_user (user_id),
    INDEX idx_lead (lead_id),
    INDEX idx_next (next_send_at, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "OK drip_enrollments table created\n";

$db->exec("DROP TABLE IF EXISTS drip_email_log");
$db->exec("CREATE TABLE drip_email_log (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    enrollment_id INT(11) NOT NULL,
    campaign_id INT(11) NOT NULL,
    email_id INT(11) NOT NULL,
    user_id BIGINT(20) UNSIGNED NULL,
    lead_id INT(11) NULL,
    status ENUM('queued','sent','delivered','opened','clicked','failed','bounced') NOT NULL DEFAULT 'queued',
    sent_at TIMESTAMP NULL,
    opened_at TIMESTAMP NULL,
    clicked_at TIMESTAMP NULL,
    error_message TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_enrollment (enrollment_id, status),
    INDEX idx_campaign (campaign_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "OK drip_email_log table created\n";

$db->exec("INSERT INTO drip_campaigns (name, description, trigger_event, status, created_by) VALUES
('Welcome Series', '3-email welcome sequence for new leads', 'new_lead', 'active', NULL),
('Property Inquiry Follow-up', '5-email sequence for property inquiries', 'property_inquiry', 'active', NULL),
('Re-engagement Campaign', 'Win back inactive leads', 'inactive_lead', 'active', NULL),
('Post Site Visit', 'After a visit, nurture the lead', 'post_visit', 'draft', NULL)
ON DUPLICATE KEY UPDATE name=name");
echo "OK 4 sample campaigns inserted\n";

$campaignId = (int)$db->query("SELECT id FROM drip_campaigns WHERE name = 'Welcome Series' LIMIT 1")->fetchColumn();
if ($campaignId && (int)$db->query("SELECT COUNT(*) FROM drip_emails WHERE campaign_id = $campaignId")->fetchColumn() === 0) {
    $db->exec("INSERT INTO drip_emails (campaign_id, sequence_order, delay_days, delay_hours, subject, body, channel) VALUES
    ($campaignId, 1, 0, 0, 'Welcome to APS Dream Home!', 'Hi {{name}},\n\nWelcome to APS Dream Home! We help you find your dream property in North India.\n\nBrowse properties: {{link}}\n\nTalk soon!', 'email'),
    ($campaignId, 2, 2, 0, 'Top 5 Properties This Week', 'Hi {{name}},\n\nHere are 5 trending properties that might interest you.\n\nView: {{link}}\n\nLet us know if you have questions!', 'email'),
    ($campaignId, 3, 5, 0, 'Need Help Finding the Right Property?', 'Hi {{name}},\n\nWe noticed you are still searching. Let our experts help you find the perfect property.\n\nSchedule a free consultation: {{link}}\n\nCheers,\nAPS Dream Home', 'email')");
    echo "OK 3 welcome emails seeded\n";
}

$campaignId2 = (int)$db->query("SELECT id FROM drip_campaigns WHERE name = 'Property Inquiry Follow-up' LIMIT 1")->fetchColumn();
if ($campaignId2 && (int)$db->query("SELECT COUNT(*) FROM drip_emails WHERE campaign_id = $campaignId2")->fetchColumn() === 0) {
    $db->exec("INSERT INTO drip_emails (campaign_id, sequence_order, delay_days, delay_hours, subject, body, channel) VALUES
    ($campaignId2, 1, 0, 1, 'Thanks for your inquiry!', 'Hi {{name}},\n\nWe received your inquiry. Our team will contact you within 24 hours.\n\nIn the meantime, here are similar properties: {{link}}', 'email'),
    ($campaignId2, 2, 1, 0, 'Did you have a chance to review?', 'Hi {{name}},\n\nJust following up on the property you inquired about. Any questions?\n\nSchedule a visit: {{link}}', 'email'),
    ($campaignId2, 3, 3, 0, 'Special offer just for you', 'Hi {{name}},\n\nWe have a special offer on the property you liked. Reply to this email to claim!', 'email'),
    ($campaignId2, 4, 7, 0, 'Final reminder', 'Hi {{name}},\n\nThis is our last follow-up regarding your inquiry. If you would like to proceed, please contact us at {{phone}}.', 'email'),
    ($campaignId2, 5, 14, 0, 'We are here when you are ready', 'Hi {{name}},\n\nWhenever you are ready to take the next step, we are here. Visit our site: {{link}}', 'email')");
    echo "OK 5 inquiry follow-up emails seeded\n";
}

echo "DONE\n";?>