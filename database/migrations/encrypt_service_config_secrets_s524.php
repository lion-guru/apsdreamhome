<?php
// Encrypt all plaintext is_secret rows in service_configs (idempotent).
//
// SAFETY CONTRACT (do not bypass):
//  1. A full safety dump MUST exist before running, e.g.
//       mysqldump -u root apsdreamhome > backups/db/service_configs_pre_encrypt_s524.sql
//  2. Only ONE actor may run this at a time; announce the DB window.
//  3. The whole migration is a single transaction. Any round-trip mismatch
//     aborts with ROLLBACK - nothing is written on failure.
//  4. Decrypt round-trip assertion per row: decryptValue(encryptValue(x)) === x.
//  5. Rows already in enc:v1: format are SKIPPED (re-runnable).
//  6. Empty strings are never touched.
//  7. Requires explicit env consent: APS_ALLOW_LIVE_ENCRYPT=1 \
//       php database/migrations/encrypt_service_config_secrets_s524.php
//  8. Rollback path (if ever needed): every stored value is decryptable via
//     ServiceConfigService::decryptValue(); the pre-dump can restore plaintext byte-exactly.

require_once __DIR__ . '/../../config/bootstrap.php';
use App\Core\Database\Database;
use App\Services\ServiceConfigService;

if (getenv('APS_ALLOW_LIVE_ENCRYPT') !== '1') {
    fwrite(STDERR, "Refusing to run against the live database.\n");
    fwrite(STDERR, "Announce the DB window, take the safety dump, then run with:\n");
    fwrite(STDERR, "  APS_ALLOW_LIVE_ENCRYPT=1 php database/migrations/encrypt_service_config_secrets_s524.php\n");
    exit(1);
}

$pdo = Database::getInstance()->getConnection();

$rows = $pdo->query(
    "SELECT id, group_name, service_name, config_key, config_value, is_secret
     FROM service_configs
     WHERE is_secret = 1
     ORDER BY group_name, service_name, config_key"
)->fetchAll(PDO::FETCH_ASSOC);

$stat = ['already_enc' => 0, 'encrypted' => 0, 'empty_skipped' => 0, 'errors' => 0];

$pdo->beginTransaction();
try {
    foreach ($rows as $r) {
        $id    = (int) $r['id'];
        $value = (string) $r['config_value'];

        if ($value === '') { $stat['empty_skipped']++; continue; }
        if (strpos($value, 'enc:v1:') === 0) { $stat['already_enc']++; continue; }

        $enc = ServiceConfigService::encryptValue($value);
        $dec = ServiceConfigService::decryptValue($enc);

        if ($dec !== $value) {
            throw new RuntimeException("Round-trip decrypt mismatch on id=$id ({$r['group_name']}.{$r['service_name']}.{$r['config_key']})");
        }

        $pdo->prepare("UPDATE service_configs SET config_value = ?, updated_at = NOW() WHERE id = ?")
            ->execute([$enc, $id]);

        $stat['encrypted']++;
    }

    $pdo->commit();

    echo "OK.\n";
    echo "  already enc:v1:  {$stat['already_enc']}\n";
    echo "  encrypted now:   {$stat['encrypted']}\n";
    echo "  empty skipped:   {$stat['empty_skipped']}\n";
    echo "  errors:          {$stat['errors']}\n";
    echo "Rollback path: every value is decryptable with ServiceConfigService::decryptValue().\n";
    echo "If you need plaintext back for any row, the pre-migration dump restores it byte-exactly.\n";
    exit(0);
} catch (Throwable $e) {
    $pdo->rollBack();
    $stat['errors']++;
    fwrite(STDERR, "ABORTED - rolled back, zero rows changed.\n");
    fwrite(STDERR, "Reason: " . $e->getMessage() . "\n");
    exit(1);
}
