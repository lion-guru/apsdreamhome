<?php
/**
 * MLM Network Rebuild Utility
 * ----------------------------
 * Usage examples:
 *   php tools/rebuild_network.php --user=42
 *   php tools/rebuild_network.php --batch=legacy_august --only-updated
 *   php tools/rebuild_network.php --batch=legacy_august --dry-run
 *
 * Rebuilds ancestor relationships for the supplied users by reading the latest
 * sponsor information from `mlm_profiles`.
 */

require_once __DIR__ . '/../includes/config.php';

if (php_sapi_name() !== 'cli') {
    fwrite(STDERR, "This script must be run from CLI.\n");
    exit(1);
}

$options = getopt('', [
    'user::',          // single user id
    'batch::',         // audit batch reference
    'only-updated::',  // flag to restrict to audit status success
    'dry-run',         // preview without modifying
    'limit::'          // max distinct users to rebuild
]);

$config = AppConfig::getInstance();
$conn = $config->getDatabaseConnection();

if (!$conn) {
    fwrite(STDERR, "Database connection failed.\n");
    exit(1);
}

$dryRun = array_key_exists('dry-run', $options);
$limit = isset($options['limit']) ? (int) $options['limit'] : null;
$users = [];

if (!empty($options['user'])) {
    $users[] = (int) $options['user'];
} else {
    $batch = $options['batch'] ?? null;
    $onlyUpdated = array_key_exists('only-updated', $options);

    $query = 'SELECT DISTINCT user_id FROM mlm_import_audit';
    $criteria = [];
    $params = [];
    $types = '';

    if ($batch) {
        $criteria[] = 'batch_reference = ?';
        $types .= 's';
        $params[] = $batch;
    }

    if ($onlyUpdated) {
        $criteria[] = "status = 'success'";
    }

    if ($criteria) {
        $query .= ' WHERE ' . implode(' AND ', $criteria);
    }

    if ($limit) {
        $query .= ' LIMIT ?';
        $types .= 'i';
        $params[] = $limit;
    }

    $stmt = $conn->prepare($query);

    if ($types) {
        $stmt->bind_param($types, ...$params);
    }

    if ($stmt->execute()) {
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        foreach ($result as $row) {
            $users[] = (int) $row['user_id'];
        }
    }

    $stmt->close();
}

$users = array_values(array_unique(array_filter($users)));

if (empty($users)) {
    fwrite(STDOUT, "No users found for rebuild.\n");
    exit(0);
}

$stats = ['processed' => 0, 'success' => 0, 'errors' => 0, 'dry_run' => $dryRun];

foreach ($users as $userId) {
    $stats['processed']++;

    try {
        $result = rebuildUserNetwork($conn, $userId, $dryRun);
        if ($result) {
            $stats['success']++;
        } else {
            $stats['errors']++;
        }
    } catch (Exception $e) {
        $stats['errors']++;
        fwrite(STDERR, sprintf("User %d failed: %s\n", $userId, $e->getMessage()));
    }
}

fwrite(STDOUT, "\nNetwork rebuild summary:\n");
fwrite(STDOUT, str_repeat('-', 32) . "\n");
fwrite(STDOUT, sprintf("Processed: %d\n", $stats['processed']));
fwrite(STDOUT, sprintf("Success:   %d\n", $stats['success']));
fwrite(STDOUT, sprintf("Errors:    %d\n", $stats['errors']));

if ($dryRun) {
    fwrite(STDOUT, "\nDRY RUN MODE - no records changed.\n");
}

exit($stats['errors'] > 0 ? 1 : 0);

/**
 * Rebuild ancestor entries for a single user.
 */
function rebuildUserNetwork(mysqli $conn, int $userId, bool $dryRun = false): bool
{
    $stmt = $conn->prepare('SELECT sponsor_user_id FROM mlm_profiles WHERE user_id = ?');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        fwrite(STDERR, sprintf("User %d missing MLM profile, skipping.\n", $userId));
        return false;
    }

    $sponsorId = $row['sponsor_user_id'];

    if ($dryRun) {
        fwrite(STDOUT, sprintf("[dry-run] Would rebuild network for user %d\n", $userId));
        return true;
    }

    $conn->begin_transaction();

    try {
        // Remove existing ancestor rows for this user
        $deleteStmt = $conn->prepare('DELETE FROM mlm_network_tree WHERE descendant_user_id = ?');
        $deleteStmt->bind_param('i', $userId);
        $deleteStmt->execute();
        $deleteStmt->close();

        if ($sponsorId) {
            $level = 1;
            $current = $sponsorId;

            while ($current) {
                $insertStmt = $conn->prepare('INSERT INTO mlm_network_tree (ancestor_user_id, descendant_user_id, level, created_at) VALUES (?, ?, ?, NOW())');
                $insertStmt->bind_param('iii', $current, $userId, $level);
                $insertStmt->execute();
                $insertStmt->close();

                $level++;

                // Move up the chain
                $stmt = $conn->prepare('SELECT sponsor_user_id FROM mlm_profiles WHERE user_id = ?');
                $stmt->bind_param('i', $current);
                $stmt->execute();
                $ancestor = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if ($ancestor && $ancestor['sponsor_user_id']) {
                    $current = (int) $ancestor['sponsor_user_id'];
                } else {
                    break;
                }
            }
        }

        $conn->commit();
        fwrite(STDOUT, sprintf("Rebuilt network for user %d\n", $userId));
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        fwrite(STDERR, sprintf("Failed rebuilding user %d: %s\n", $userId, $e->getMessage()));
        return false;
    }
}
