<?php
/**
 * cron_dup_lead_detect.php — fuzzy duplicate lead detection (AI employee).
 * Finds live leads sharing phone OR normalized name+city, flags with note.
 * Usage: php scripts/cron_dup_lead_detect.php [--dry-run]
 */
require_once dirname(__DIR__) . '/config/bootstrap_cli.php';

$dryRun = in_array('--dry-run', $argv ?? [], true);
$db = App\Core\Database\Database::getInstance();

// Same phone, live, non-empty
$byPhone = $db->fetchAll("
    SELECT phone, COUNT(*) as n,
           GROUP_CONCAT(id ORDER BY id) as ids
    FROM leads
    WHERE deleted_at IS NULL AND phone IS NOT NULL AND phone != ''
    GROUP BY phone HAVING n > 1
") ?: [];

// Same normalized name + city
$byName = $db->fetchAll("
    SELECT LOWER(TRIM(name)) as nname, city, COUNT(*) as n,
           GROUP_CONCAT(id ORDER BY id) as ids
    FROM leads
    WHERE deleted_at IS NULL AND name IS NOT NULL AND name != '' AND city IS NOT NULL AND city != ''
    GROUP BY nname, city HAVING n > 1
") ?: [];

$groups = array_merge($byPhone, $byName);
echo "Duplicate groups: " . count($groups) . "\n";

$flagged = 0;
foreach ($groups as $g) {
    $ids = array_map('intval', explode(',', $g['ids']));
    $primary = array_shift($ids);
    foreach ($ids as $dupId) {
        if ($dryRun) {
            echo "dry-run: lead $dupId dup of $primary\n";
            continue;
        }
        // Flag, not delete (families share phones) — audit note for admin review
        $db->execute(
            "INSERT INTO lead_activities (lead_id, activity_type, description, created_by) VALUES (?, 'note', ?, '0')",
            [$dupId, "[dup-detect] Same contact as lead #$primary — admin review"]
        );
        $flagged++;
    }
    echo "group primary=$primary dups=" . count($ids) . "\n";
}
echo ($dryRun ? "Dry" : "Archived") . ": $flagged duplicates\n";
exit(0);
