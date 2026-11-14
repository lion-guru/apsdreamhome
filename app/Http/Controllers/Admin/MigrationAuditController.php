<?php
/**
 * Migration Audit — READ-ONLY visibility into the schema_migrations table
 * and the migration files on disk.
 *
 * The project claims "migration-only schema" (standing rule) but the live
 * DB only tracks 4 of 234 migrations. This page surfaces that gap and
 * shows a snapshot of the tables the codebase references but may not
 * have in the live database.
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;

class MigrationAuditController extends BaseController
{
    protected string $layout = 'layouts/admin';

    public function index(): void
    {
        $this->requireAdmin();

        $db = Database::getInstance();

        // --- 1) Recorded runs
        $applied = $db->fetchAll("SELECT version, name, executed_at FROM schema_migrations ORDER BY executed_at") ?: [];

        // --- 2) Files on disk
        $migrationDir = realpath(__DIR__ . '/../../../../database/migrations') . '/';
        $diskFiles = [];
        if (is_dir($migrationDir)) {
            foreach (scandir($migrationDir) as $f) {
                if ($f === '.' || $f === '..' || substr($f, -4) !== '.php') continue;
                $diskFiles[] = [
                    'name' => $f,
                    'size' => filesize($migrationDir . $f),
                    'mtime' => filemtime($migrationDir . $f),
                    'head' => @file_get_contents($migrationDir . $f, false, null, 0, 400) ?: '',
                ];
            }
        }
        sort($diskFiles);

        // --- 3) Cross-reference: does a recorded version appear in a file name?
        $recordedNames = [];
        foreach ($applied as $row) $recordedNames[] = strtolower((string)$row['version']);

        $matches = 0; $unknown = [];
        foreach ($diskFiles as &$df) {
            $matched = false;
            $base = strtolower(preg_replace('/^[0-9_-]+/', '', pathinfo($df['name'], PATHINFO_FILENAME)));
            foreach ($recordedNames as $rn) {
                if ($rn !== '' && (strpos(strtolower($df['name']), $rn) !== false || strpos($rn, $base) !== false)) {
                    $matched = true; break;
                }
            }
            $df['tracked'] = $matched;
            if ($matched) $matches++; else $unknown[] = $df['name'];
        }
        unset($df);

        // --- 4) Loose audit: top-level tables the code references that may NOT exist
        $liveTables = [];
        foreach ($db->fetchAll("SHOW TABLES") as $row) {
            $t = array_values($row)[0] ?? '';
            if ($t !== '') $liveTables[strtolower($t)] = true;
        }
        $missingRefs = [];
        foreach (['schema_migrations'] as $required) {
            if (!isset($liveTables[$required])) $missingRefs[] = $required;
        }

        $this->render('admin/migrations_dashboard', [
            'page_title' => 'Migration Audit',
            'page_heading' => 'Migration Audit',
            'page_subtitle' => 'Read-only visibility into schema management',
            'applied' => $applied,
            'diskFiles' => $diskFiles,
            'matches' => $matches,
            'unknownCount' => count($unknown),
            'unknown' => $unknown,
            'liveTableCount' => count($liveTables),
            'missingRefs' => $missingRefs,
        ]);
    }
}
