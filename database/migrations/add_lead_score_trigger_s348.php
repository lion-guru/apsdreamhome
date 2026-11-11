<?php
/**
 * Migration: Auto-lead-scoring trigger via DB events
 * 
 * NOTE: MySQL triggers are limited. Instead, we use an application-level
 * observer pattern. This migration just ensures the lead_scores table has
 * the right indexes for fast lookups.
 * 
 * Idempotent: CREATE INDEX IF NOT EXISTS (MariaDB 10.5+)
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

// Ensure lead_scores has proper indexes for fast auto-scoring lookups
try {
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_lead_scores_lead_id ON lead_scores (lead_id)");
} catch (\Throwable $e) {
    // Index might already exist
}

try {
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_leads_status_score ON leads (status, lead_score)");
} catch (\Throwable $e) {
    // Index might already exist
}

echo "DONE: Lead scoring indexes ensured.\n";