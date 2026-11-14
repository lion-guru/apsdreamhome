<?php
/**
 * Regenerate a clean UTF-8 dump of the live database into backups/db/.
 * Safe: reads only; writes one new .sql file with a timestamp.
 * The existing files are left untouched (even broken ones used for drill evidence).
 */
$root = dirname(__DIR__);
$stamp = gmdate('Y-m-d_H-i');
$out = $root . '/backups/db/apsdreamhome_live_' . $stamp . '.sql';
$mysqlDump = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '3306';

// --skip-comments: smaller dump. --result-file: direct file output (avoids stdout encoding issues).
$raw = shell_exec(
    escapeshellcmd($mysqlDump)
    . ' --user=' . escapeshellarg($user)
    . ' --host=' . escapeshellarg($host) . ' --port=' . escapeshellarg($port)
    . ' --default-character-set=utf8mb4 --skip-comments --hex-blob --skip-extended-insert --no-tablespaces apsdreamhome'
    . ' --result-file=' . escapeshellarg($out)
    . ' 2>&1'
);
echo "dump output:\n" . ($raw ?? '(none)') . "\n";
if (!is_file($out)) { fwrite(STDERR, "dump file not created\n"); exit(1); }
echo "written: $out\n";
$bytes = filesize($out);
echo "bytes: $bytes (" . round($bytes / 1048576, 1) . " MB)\n";

// NUL byte check
$nul = 0;
$fh = fopen($out, 'rb');
while (!feof($fh)) { $chk = fread($fh, 1048576); if ($chk === false) break; $nul += substr_count($chk, "\x00"); }
fclose($fh);
echo "NUL bytes: $nul\n";
exit($nul > 0 ? 2 : 0);
