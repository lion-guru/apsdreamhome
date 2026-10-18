<?php
/**
 * Migration: Fix drip_emails.campaign_id foreign key
 * Session: 336
 *
 * PROBLEM (S285 finding, confirmed live in S336):
 *   drip_emails.campaign_id has FK -> campaigns.campaign_id
 *   But DripCampaignService writes drip_campaigns.id into that column.
 *   Result: any addEmail() for a drip id that has no colliding campaigns row
 *   dies with 1452 (foreign key constraint fails).
 *
 * FIX:
 *   Re-point the FK to drip_campaigns(id) — the real parent.
 *   Pre-checked in S336: all 5 live rows (campaign_id=2) resolve to
 *   drip_campaigns.id=2, so zero orphans. Constraint swap is data-safe.
 *
 * Idempotent: re-run detects the correct FK and prints SKIP.
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

// --- Pre-flight: orphan audit (never swap a FK that would break data) ---
$orphan = (int) $pdo->query("
    SELECT COUNT(*) AS c
    FROM drip_emails de
    LEFT JOIN drip_campaigns dc ON dc.id = de.campaign_id
    WHERE dc.id IS NULL
")->fetch()['c'];

if ($orphan > 0) {
    echo "ABORT: {$orphan} drip_emails row(s) have no matching drip_campaigns.id — FK swap would fail.\n";
    echo "Fix data first (map campaign_id -> drip_campaigns.id), then re-run this migration.\n";
    exit(1);
}

// --- Current FK target ---
$current = $pdo->query("
    SELECT REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'drip_emails'
      AND COLUMN_NAME = 'campaign_id'
      AND REFERENCED_TABLE_NAME IS NOT NULL
    LIMIT 1
")->fetch();

$currentTable = $current['REFERENCED_TABLE_NAME'] ?? null;

if ($currentTable === 'drip_campaigns') {
    echo "SKIP: drip_emails.campaign_id already FK -> drip_campaigns(id) — nothing to do.\n";
    exit(0);
}

echo "CHANGE: drip_emails.campaign_id FK is currently -> " . ($currentTable ?: 'NONE') . ", re-pointing to drip_campaigns(id)\n";

// --- Ensure an index exists on the parent join column ---
$pdo->exec("CREATE INDEX IF NOT EXISTS idx_drip_campaigns_id ON drip_campaigns (id)");

// --- Drop the wrong FK, add the correct one ---
$pdo->exec("ALTER TABLE drip_emails DROP FOREIGN KEY fk_drip_emails_campaign_id");
echo "  dropped FK fk_drip_emails_campaign_id\n";

$pdo->exec("
    ALTER TABLE drip_emails
    ADD CONSTRAINT fk_drip_emails_campaign_id
    FOREIGN KEY (campaign_id) REFERENCES drip_campaigns (id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
");
echo "  added FK fk_drip_emails_campaign_id -> drip_campaigns(id) ON DELETE CASCADE\n";

// --- Verify ---
$verify = $pdo->query("
    SELECT REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'drip_emails'
      AND COLUMN_NAME = 'campaign_id'
      AND REFERENCED_TABLE_NAME IS NOT NULL
    LIMIT 1
")->fetch();

if (($verify['REFERENCED_TABLE_NAME'] ?? '') === 'drip_campaigns') {
    echo "DONE: drip_emails.campaign_id -> drip_campaigns(id) verified.\n";
} else {
    echo "FAIL: post-verification did not find the new FK. Inspect manually.\n";
    exit(1);
}