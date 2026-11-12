<?php
/**
 * Scheduled job registry.
 *
 * NOTE (S453): App\Services\BackupRestoreService::scheduleBackup() reads
 * this file with `include` and then rewrites it with file_put_contents().
 * The file was never tracked, so on a fresh checkout (and on the CI runner)
 * the `include` referred to a file that did not exist.
 *
 * The format is a plain PHP array of job descriptors:
 *
 *   [
 *       'type'       => 'full',              // full | incremental
 *       'frequency'  => 'daily',             // daily | weekly | monthly
 *       'time'       => '02:00',
 *       'created_by' => 1,
 *       'created_at' => '2026-11-12 02:00:00',
 *       'active'     => true,
 *   ]
 *
 * It is intentionally empty - jobs are added by scheduleBackup().
 */

return [];
