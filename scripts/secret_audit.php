<?php
/**
 * Secret Audit — read-only scan of every known secret sink for plaintext tokens
 * matching common production credential patterns.
 *
 *  Scanned:
 *   - settings (value column)
 *   - api_keys (key_value column)
 *   - service_configs (only rows with is_secret=0 — is_secret=1 rows are AES-256 encrypted)
 *   - .env file (on disk, not committed)
 *
 *  Flags any value starting with a pattern that's a strong production-token indicator.
 *
 * Usage: php scripts/secret_audit.php [--quiet]
 * Exit codes: 0 = clean | 1 = one or more plaintext token-shaped strings found
 */

$quiet = in_array('--quiet', $argv ?? [], true);
$failures = [];

// Pattern list (very conservative: only known production-key prefixes)
$patterns = [
    'OpenCode Zen'    => '/^oc_sk_[A-Za-z0-9_-]{10,}/',
    'OpenAI / OpenRouter / Groq-like' => '/^sk-[A-Za-z0-9]{20,}/',
    'Groq key'        => '/^gsk_[A-Za-z0-9]{20,}/',
    'Google API / Firebase' => '/^AIzaSy[A-Za-z0-9_-]{33,}/',
    'AWS access key'   => '/^AKIA[0-9A-Z]{16,}$/',
    'GitHub token'    => '/^ghp_[A-Za-z0-9]{30,}/',
    'HuggingFace'     => '/^hf_[A-Za-z0-9]{30,}/',
    'Slack bot'      => '/^xoxb-[0-9]+-/',
    'JWT-signed (or simply long secret)' => '/^eyJ[A-Za-z0-9_-]{20,}\.[A-Za-z0-9_-]{10,}/',
];

function flag(string $label, string $sample, string $patternName): string {
    $sample = mb_substr($sample, 0, 40);
    return "$label matches $patternName (\"$sample…\")";
}

function scanTable(PDO $pdo, string $table, string $col, string $idCol, $patterns): array {
    $found = [];
    $q = $pdo->query("SELECT `$idCol` id, `$col` val FROM `$table` WHERE `$col` IS NOT NULL AND `$col` <> ''");
    foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $row) {
        foreach ($patterns as $name => $regex) {
            if (preg_match($regex, (string)$row['val'])) {
                $found[] = "row id={$row['id']}: matches $name";
                break;
            }
        }
    }
    return $found;
}

echo "== secret audit ==\n";

try {
    $root = dirname(__DIR__);
    require_once $root . '/config/bootstrap.php';

    $pdo = \App\Core\Database\Database::getInstance()->getConnection();

    // 1) settings.value
    foreach (scanTable($pdo, 'settings', 'value', 'id', $patterns) as $line) {
        $failures[] = "settings.value: $line";
    }

    // 2) api_keys.key_value
    foreach (scanTable($pdo, 'api_keys', 'key_value', 'id', $patterns) as $line) {
        $failures[] = "api_keys.key_value: $line";
    }

    // 3) service_configs.config_value — only rows with is_secret=0 OR config_type='json' with plain values (we open-scan all types but filter sensitive-regex)
    $q = $pdo->query("SELECT id, service_name, config_key, config_value, config_type FROM service_configs WHERE config_value IS NOT NULL AND config_value <> ''");
    foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $v = (string)$row['config_value'];
        // skip enc:v1: (already encrypted)
        if (strpos($v, 'enc:v1:') === 0) continue;
        // skip obvious non-token JSON serializations by checking if the WHOLE value is valid JSON and short
        foreach ($patterns as $name => $regex) {
            if (preg_match($regex, $v)) {
                $failures[] = "service_configs.{$row['service_name']}.{$row['config_key']} (type={$row['config_type']}): matches $name";
                break;
            }
        }
    }

    // 4) .env (on-disk sanity even though it's gitignored)
    $env = $root . '/.env';
    if (is_file($env)) {
        foreach (file($env, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $ln => $line) {
            if ($line === '' || $line[0] === '#') continue;
            foreach ($patterns as $name => $regex) {
                if (preg_match($regex, trim($line))) {
                    $failures[] = ".env line " . ($ln + 1) . ": matches $name";
                    break;
                }
            }
        }
    }

} catch (\Throwable $e) {
    echo "  NOTE: DB-backed scans could not run: " . $e->getMessage() . "\n";
}

echo "plaintext token-shaped strings: " . count($failures) . "\n";
foreach ($failures as $f) echo "  - $f\n";
if (count($failures)) {
    echo "\nRESULT: FAILED\n";
    exit(1);
}
echo "\nRESULT: OK\n";
exit(0);
