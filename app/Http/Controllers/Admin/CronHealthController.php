<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

class CronHealthController extends AdminController
{
    public function index()
    {
        $logFile = STORAGE_PATH . '/logs/master_cron.log';
        $entries = [];

        if (file_exists($logFile)) {
            $lines = file($logFile);
            $lines = array_reverse($lines); // newest first
            foreach (array_slice($lines, 0, 50) as $line) {
                if (trim($line)) {
                    $parts = explode(' | ', $line, 4);
                    if (count($parts) >= 4) {
                        $entries[] = [
                            'timestamp' => $parts[0],
                            'mode' => $parts[1],
                            'data' => json_decode($parts[3], true) ?: [],
                        ];
                    }
                }
            }
        }

        // Check if cron is running (last run time)
        $lastRun = null;
        if (!empty($entries)) {
            $lastRun = $entries[0]['timestamp'] ?? null;
        }

        // Check Windows Task Scheduler status (basic check)
        $cronStatus = $this->checkCronStatus();

        return $this->render('admin/cron-health/index', [
            'page_title' => 'Cron Health Monitor',
            'entries' => $entries,
            'last_run' => $lastRun,
            'cron_status' => $cronStatus,
        ]);
    }

    public function run()
    {
        $this->requireAdmin();

        $mode = $_POST['mode'] ?? 'daily';
        $dryRun = isset($_POST['dry_run']);

        $cmd = "php " . BASE_PATH . "/scripts/run_all_crons.php --mode={$mode}";
        if ($dryRun) {
            $cmd .= " --dry-run";
        }

        // Run in background
        exec($cmd . " > /dev/null 2>&1 &");

        $this->setFlash('success', "Cron job started in {$mode} mode" . ($dryRun ? " (dry run)" : ""));
        $this->redirect('/admin/cron-health');
    }

    public function status()
    {
        $this->requireAdmin();

        $logFile = STORAGE_PATH . '/logs/master_cron.log';
        $entries = [];

        if (file_exists($logFile)) {
            $lines = file($logFile);
            $lines = array_reverse($lines);
            foreach (array_slice($lines, 0, 10) as $line) {
                if (trim($line)) {
                    $parts = explode(' | ', $line, 4);
                    if (count($parts) >= 4) {
                        $entries[] = [
                            'timestamp' => $parts[0],
                            'mode' => $parts[1],
                            'data' => json_decode($parts[3], true) ?: [],
                        ];
                    }
                }
            }
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'last_run' => $entries[0]['timestamp'] ?? null,
            'last_mode' => $entries[0]['mode'] ?? null,
            'recent' => $entries,
        ]);
        exit;
    }

    private function checkCronStatus(): array
    {
        $status = [
            'daily_task' => false,
            'monthly_task' => false,
            'last_daily_run' => null,
            'last_monthly_run' => null,
            'log_file_exists' => file_exists(STORAGE_PATH . '/logs/master_cron.log'),
            'log_file_writable' => is_writable(dirname(STORAGE_PATH . '/logs/master_cron.log')),
        ];

        $logFile = STORAGE_PATH . '/logs/master_cron.log';
        if (file_exists($logFile)) {
            $lines = file($logFile);
            $lines = array_reverse($lines);
            foreach ($lines as $line) {
                if (trim($line)) {
                    $parts = explode(' | ', $line, 4);
                    if (count($parts) >= 4) {
                        $data = json_decode($parts[3], true);
                        if ($data && isset($data['mode'])) {
                            if ($data['mode'] === 'DAILY' && !$status['last_daily_run']) {
                                $status['last_daily_run'] = $parts[0];
                                $status['daily_task'] = true;
                            }
                            if ($data['mode'] === 'MONTHLY' && !$status['last_monthly_run']) {
                                $status['last_monthly_run'] = $parts[0];
                                $status['monthly_task'] = true;
                            }
                        }
                        if ($status['last_daily_run'] && $status['last_monthly_run']) break;
                    }
                }
            }
        }

        return $status;
    }
}