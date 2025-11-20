<?php
/**
 * Sponsor Import Utility (Phase 2)
 * ---------------------------------
 * Usage examples:
 *   php database/import_sponsors.php --file=data/sponsors.csv --batch=legacy_august
 *   php database/import_sponsors.php --table=legacy_sponsors --dry-run
 *
 * CSV columns expected (header row optional but recommended):
 *   user_id,sponsor_user_id,referral_code,notes
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../app/services/ReferralService.php';

if (php_sapi_name() !== 'cli') {
    fwrite(STDERR, "This script must be run from the command line.\n");
    exit(1);
}

$options = getopt('', [
    'file:',        // Path to CSV file
    'table::',      // Legacy table name to import from
    'batch::',      // Optional batch reference name
    'delimiter::',  // CSV delimiter (default ,)
    'dry-run',      // Preview without updating
    'limit::'       // Limit rows processed
]);

$sourceFile = $options['file'] ?? null;
$legacyTable = $options['table'] ?? null;
$batchReference = $options['batch'] ?? null;
$delimiter = $options['delimiter'] ?? ',';
$dryRun = array_key_exists('dry-run', $options);
$limit = isset($options['limit']) ? (int) $options['limit'] : null;

if (!$sourceFile && !$legacyTable) {
    fwrite(STDERR, "Provide either --file=<path> or --table=<name> as input.\n");
    exit(1);
}

$config = AppConfig::getInstance();
$conn = $config->getDatabaseConnection();

if (!$conn) {
    fwrite(STDERR, "Database connection failed.\n");
    exit(1);
}

$referralService = new ReferralService();
$records = [];

try {
    if ($sourceFile) {
        $records = loadFromCsv($sourceFile, $delimiter);
    } else {
        $records = loadFromTable($conn, $legacyTable, $limit);
    }
} catch (Exception $e) {
    fwrite(STDERR, "Import preparation failed: {$e->getMessage()}\n");
    exit(1);
}

if ($limit !== null && $limit > 0 && count($records) > $limit) {
    $records = array_slice($records, 0, $limit);
}

if (empty($records)) {
    fwrite(STDOUT, "No records found to import.\n");
    exit(0);
}

$summary = $referralService->bulkAssignSponsors(
    $records,
    $batchReference,
    [
        'dry_run' => $dryRun,
        'log_dry_run' => true
    ]
);

fwrite(STDOUT, "\nSponsor import summary:\n");
fwrite(STDOUT, str_repeat('-', 40) . "\n");
fwrite(STDOUT, sprintf("Batch: %s\n", $summary['batch_reference']));
fwrite(STDOUT, sprintf("Processed: %d\n", $summary['processed']));
fwrite(STDOUT, sprintf("Success:   %d\n", $summary['success']));
fwrite(STDOUT, sprintf("Skipped:   %d\n", $summary['skipped']));
fwrite(STDOUT, sprintf("Errors:    %d\n", $summary['errors']));

if ($dryRun) {
    fwrite(STDOUT, "\nDRY RUN MODE - no data was modified.\n");
}

exit($summary['errors'] > 0 ? 1 : 0);

/**
 * Load sponsor records from a CSV file.
 */
function loadFromCsv(string $filePath, string $delimiter = ','): array
{
    if (!file_exists($filePath)) {
        throw new RuntimeException("CSV file not found: {$filePath}");
    }

    $handle = fopen($filePath, 'r');
    if (!$handle) {
        throw new RuntimeException("Unable to open CSV file: {$filePath}");
    }

    $records = [];
    $header = null;

    while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
        if ($header === null) {
            // Detect header: if row contains non-numeric in user_id column
            if (count($row) >= 1 && !ctype_digit(trim((string) $row[0]))) {
                $header = array_map('trim', $row);
                continue;
            } else {
                $header = ['user_id', 'sponsor_user_id', 'referral_code', 'notes'];
            }
        }

        $record = array_combine(
            array_pad($header, count($row), null),
            array_map('trim', $row)
        );

        // Normalise keys
        $records[] = [
            'user_id' => $record['user_id'] ?? $record['USER_ID'] ?? null,
            'sponsor_user_id' => $record['sponsor_user_id'] ?? $record['SPONSOR_USER_ID'] ?? null,
            'referral_code' => $record['referral_code'] ?? $record['REFERRAL_CODE'] ?? null,
            'notes' => $record['notes'] ?? $record['NOTES'] ?? null
        ];
    }

    fclose($handle);

    return $records;
}

/**
 * Load sponsor records from a legacy table.
 */
function loadFromTable(mysqli $conn, string $tableName, ?int $limit = null): array
{
    $stmt = $conn->prepare(
        sprintf(
            'SELECT user_id, sponsor_user_id, referral_code, notes FROM %s',
            preg_replace('/[^a-zA-Z0-9_]/', '', $tableName)
        ) . ($limit ? ' LIMIT ?' : '')
    );

    if ($limit) {
        $stmt->bind_param('i', $limit);
    }

    if (!$stmt->execute()) {
        throw new RuntimeException('Failed to read from legacy table: ' . $stmt->error);
    }

    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $result;
}
