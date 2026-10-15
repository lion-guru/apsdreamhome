<?php
/**
 * Make lead_scores.model_id NULLable (Session 248).
 * Idempotent: ALTER runs ONLY when the column is still NOT NULL.
 *
 * Background: model_id has no FK and no PHP readers (vestigial), but its
 * NOT NULL + no-default breaks every first-time saveScore() INSERT (1364),
 * so the associate "Recalculate Score" button 500'd on unscored leads.
 * NULLable = "no model used", existing values untouched.
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$row = $pdo->query(
    "SELECT IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'lead_scores' AND COLUMN_NAME = 'model_id'"
)->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo "SKIP lead_scores.model_id (column absent)\nDone.\n";
    return;
}

if ($row['IS_NULLABLE'] === 'YES') {
    echo "SKIP lead_scores.model_id (already NULLable)\nDone.\n";
    return;
}

$pdo->exec("ALTER TABLE `lead_scores` MODIFY `model_id` INT NULL DEFAULT NULL");
echo "FIXED lead_scores.model_id (now NULLable)\nDone.\n";
