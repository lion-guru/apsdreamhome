<?php
/**
 * Menu Rename: "Telecoder Performance" → "Telecaller Performance" (Session 236).
 * Idempotent: UPDATE runs ONLY when the live row still carries the typo.
 * Safe to re-run after any DB restore (restored dumps predate the fix).
 *
 * Background: browser audit caught the typo live in the admin sidebar.
 * Canonical spelling fixed in config/admin_menu_manifest.php in the same
 * session; this file carries the live-DB side so restores don't revert it.
 * Perm key (telecaller_performance) and URL are untouched — rename only.
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$row = $pdo->query(
    "SELECT id, name FROM admin_menu_items WHERE id = 285"
)->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo "SKIP menu 285 (row absent)\n";
} elseif (($row['name'] ?? '') === 'Telecaller Performance') {
    echo "SKIP menu 285 (already renamed)\n";
} else {
    $stmt = $pdo->prepare("UPDATE admin_menu_items SET name = 'Telecaller Performance' WHERE id = 285");
    $stmt->execute();
    echo "FIXED menu 285 ('{$row['name']}' → 'Telecaller Performance')\n";
}

echo "Done.\n";
