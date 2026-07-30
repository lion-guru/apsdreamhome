<?php
/**
 * Autonomous Agentic Dev System - Main Orchestrator
 * Continuous multi-agent development pipeline for APS Dream Home
 * Runs without user intervention - works while you sleep
 */

namespace App\Core\Agentic;

use App\Core\Database;
use App\Core\Autoloader;

class Orchestrator
{
    private $config;
    private $db;
    private $running = false;
    private $cycle = 0;
    private $logFile;
    private $stateFile;

    public function __construct()
    {
        $this->config = json_decode(file_get_contents(__DIR__ . '/../config.json'), true);
        $this->db = Database::getInstance();
        $this->logFile = __DIR__ . '/../logs/agent_heartbeat.log';
        $this->stateFile = __DIR__ . '/../state/agent_state.json';

        if (!is_dir(dirname($this->logFile))) {
            mkdir(dirname($this->logFile), 0777, true);
        }
        if (!is_dir(dirname($this->stateFile))) {
            mkdir(dirname($this->stateFile), 0777, true);
        }
    }

    public function run(): void
    {
        $this->running = true;
        $this->log("=== AUTONOMOUS AGENTIC DEV SYSTEM STARTED ===");
        $this->log("Project: " . $this->config['project']);
        $this->log("Agents: " . count($this->config['agents']));
        $this->log("Mode: " . $this->config['scheduler']['mode']);
        $this->log("Cycle interval: " . $this->config['scheduler']['cycle_interval_ms'] . "ms");

        $maxCycles = $this->config['scheduler']['max_cycles_per_run'];
        $interval = $this->config['scheduler']['cycle_interval_ms'];

        while ($this->running && $this->cycle < $maxCycles) {
            $this->cycle++;
            $this->log("--- CYCLE {$this->cycle} ---");

            try {
                $this->discoverTasks();
                $this->executeCycle();
                $this->runE2ETests();
                $this->checkOllama();
                $this->saveState();
                $this->reportProgress();
            } catch (\Exception $e) {
                $this->log("ERROR in cycle {$this->cycle}: " . $e->getMessage());
                $this->saveState();
            }

            if ($this->cycle < $maxCycles) {
                usleep($interval * 1000);
            }
        }

        $this->log("=== ORCHESTRATOR STOPPED (cycle {$this->cycle}/{$maxCycles}) ===");
    }

    private function discoverTasks(): void
    {
        $this->log("Task discovery...");

        $tasks = [];

        // 1. Check git diff for uncommitted changes
        $gitDiff = @shell_exec('cd /d "' . $this->config['project_root'] . '" && git diff --stat HEAD~3 2>&1');
        if (!empty($gitDiff) && trim($gitDiff) !== '') {
            $tasks[] = ['type' => 'code_change', 'desc' => 'Recent changes detected', 'source' => 'git diff'];
        }

        // 2. Check PHP syntax errors
        $phpErrors = @shell_exec('cd /d "' . $this->config['project_root'] . '" && find app/Services app/Http -name "*.php" -exec php -l {} \; 2>&1 | grep -i error || echo "OK"');
        if (strpos($phpErrors, 'error') !== false && trim($phpErrors) !== 'OK') {
            $tasks[] = ['type' => 'syntax_error', 'desc' => 'PHP syntax errors found', 'source' => 'php -l'];
        }

        // 3. Check AGENTS.md for pending tasks
        $agentsMd = @file_get_contents($this->config['project_root'] . '/AGENTS.md');
        if ($agentsMd && strpos($agentsMd, 'PENDING') !== false || strpos($agentsMd, 'TODO') !== false) {
            $tasks[] = ['type' => 'pending_task', 'desc' => 'Pending tasks in AGENTS.md', 'source' => 'AGENTS.md'];
        }

        // 4. Check E2E test results
        $e2eLastRun = @shell_exec('cd /d "' . $this->config['project_root'] . '" && cat testing/visual_tests/.last_result.json 2>/dev/null || echo "NO_RESULTS"');
        if (trim($e2eLastRun) === 'NO_RESULTS' || empty(trim($e2eLastRun))) {
            $tasks[] = ['type' => 'e2e_test', 'desc' => 'E2E tests not recently run', 'source' => 'e2e_tests'];
        }

        // 5. Check for dead code (files in _archive that might be needed)
        $archiveCount = @shell_exec('cd /d "' . $this->config['project_root'] . '" && find _archive -name "*.php" 2>/dev/null | wc -l');
        if ((int)$archiveCount > 0) {
            $tasks[] = ['type' => 'archive_audit', 'desc' => "{$archiveCount} files in archive to audit", 'source' => '_archive'];
        }

        if (!empty($tasks)) {
            $this->log("Discovered " . count($tasks) . " tasks: " . implode(', ', array_column($tasks, 'type')));
            $this->saveTasks($tasks);
        } else {
            $this->log("No new tasks discovered - system idle");
        }
    }

    private function executeCycle(): void
    {
        $tasks = $this->loadTasks();
        if (empty($tasks)) {
            $this->log("No tasks this cycle - running periodic checks");
            $this->runPeriodicChecks();
            return;
        }

        foreach ($tasks as $task) {
            $this->log("Executing task: {$task['type']} - {$task['desc']}");
            switch ($task['type']) {
                case 'syntax_error':
                    $this->fixSyntaxErrors();
                    break;
                case 'code_change':
                    $this->reviewChanges();
                    break;
                case 'pending_task':
                    $this->processPendingTasks();
                    break;
                case 'e2e_test':
                    $this->runE2ETests();
                    break;
                case 'archive_audit':
                    $this->auditArchive();
                    break;
                default:
                    $this->log("Unknown task type: {$task['type']}, running generic check");
                    $this->runPeriodicChecks();
            }
        }
    }

    private function runPeriodicChecks(): void
    {
        $this->log("Running periodic checks...");

        // Check PHP syntax on changed files
        $changedFiles = @shell_exec('cd /d "' . $this->config['project_root'] . '" && git diff --name-only HEAD 2>/dev/null | head -20');
        if (!empty($changedFiles)) {
            foreach (explode("\n", trim($changedFiles)) as $file) {
                if (trim($file) && strpos($file, '.php') !== false) {
                    $result = @shell_exec('php -l "' . trim($file) . '" 2>&1');
                    if (strpos($result, 'error') !== false && strpos($result, 'No syntax errors') === false) {
                        $this->log("Syntax error in {$file}: {$result}");
                    }
                }
            }
        }

        // Check E2E tests
        $this->runE2ETests();

        // Check tenant isolation
        $this->checkTenantIsolation();
    }

    private function fixSyntaxErrors(): void
    {
        $this->log("Fixing PHP syntax errors...");
        $result = @shell_exec('cd /d "' . $this->config['project_root'] . '" && find app/ -name "*.php" -exec php -l {} \; 2>&1 | grep -i "error" | grep -v "No syntax errors"');
        if (!empty($result)) {
            $this->log("Found syntax errors needing review:\n" . $result);
        } else {
            $this->log("No syntax errors found");
        }
    }

    private function reviewChanges(): void
    {
        $this->log("Reviewing recent code changes...");
        $diff = @shell_exec('cd /d "' . $this->config['project_root'] . '" && git diff HEAD~1 2>/dev/null | head -100');
        if (!empty($diff)) {
            $this->log("Recent changes (first 100 lines):\n" . substr($diff, 0, 500));
        }
    }

    private function processPendingTasks(): void
    {
        $this->log("Processing pending tasks from AGENTS.md...");
        $content = @file_get_contents($this->config['project_root'] . '/AGENTS.md');
        if ($content) {
            preg_match_all('/\| P\d+ \| \*\*([^*]+)\*\* \| (.+?) \|/s', $content, $matches);
            if (!empty($matches[1])) {
                $pending = array_filter($matches[2], fn($s) => strpos($s, 'COMPLETE') === false);
                $this->log("Pending tasks found: " . count($pending));
                foreach (array_slice($pending, 0, 5) as $task) {
                    $this->log("  - {$task}");
                }
            }
        }
    }

    private function auditArchive(): void
    {
        $this->log("Auditing archived files...");
        $archiveDir = $this->config['project_root'] . '/_archive';
        if (is_dir($archiveDir)) {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($archiveDir));
            $count = 0;
            foreach ($files as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $count++;
                }
            }
            $this->log("Found {$count} PHP files in _archive");
        }
    }

    private function checkTenantIsolation(): void
    {
        $this->log("Checking tenant isolation...");
        $tables = ['leads', 'booking_payment_schedules', 'mlm_commission_ledger', 'payments'];
        foreach ($tables as $table) {
            try {
                $stmt = $this->db->prepare("SHOW COLUMNS FROM {$table} LIKE 'tenant_id'");
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    $this->log("  {$table}: has tenant_id column OK");
                } else {
                    $this->log("  {$table}: MISSING tenant_id column!");
                }
            } catch (\Exception $e) {
                $this->log("  {$table}: CHECK FAILED - " . $e->getMessage());
            }
        }
    }

    private function runE2ETests(): void
    {
        $this->log("Running E2E tests...");
        $cmd = $this->config['e2e_tests']['command'];
        $timeout = $this->config['e2e_tests']['timeout_ms'];
        $expected = $this->config['e2e_tests']['expected_pass_rate'];

        $start = microtime(true);
        $result = @shell_exec("cd /d \"{$this->config['project_root']}\" && timeout /t " . ($timeout / 1000) . " node {$cmd} 2>&1");
        $duration = microtime(true) - $start;

        if (strpos($result, 'PASS') !== false || strpos($result, 'passed') !== false) {
            preg_match_all('/(\d+)\/(\d+)\s+(PASS|passed)/', $result, $match);
            if (!empty($match[1])) {
                $pass = (int)$match[1][count($match[1]) - 1];
                $total = (int)$match[2][count($match[2]) - 1];
                $rate = $total > 0 ? $pass / $total : 0;
                $this->log("E2E Tests: {$pass}/{$total} pass ({$rate}%) " . ($rate >= $expected ? 'OK' : 'FAIL'));

                // Save last result
                file_put_contents(
                    $this->config['project_root'] . '/testing/visual_tests/.last_result.json',
                    json_encode(['pass' => $pass, 'total' => $total, 'rate' => $rate, 'time' => $duration])
                );
            }
        } else {
            $this->log("E2E Tests: Could not run or parse results");
        }
    }

    private function checkOllama(): void
    {
        if (!$this->config['ollama']['enabled']) return;

        $this->log("Checking Ollama...");
        $model = $this->config['ollama']['model'];
        $baseUrl = $this->config['ollama']['base_url'];

        try {
            $response = @file_get_contents("{$baseUrl}/api/tags");
            if ($response) {
                $tags = json_decode($response, true);
                $models = $tags['models'] ?? [];
                $found = false;
                foreach ($models as $m) {
                    if (strpos($m['name'], $model) === 0) {
                        $found = true;
                        $this->log("  Ollama model found: {$m['name']} ({$m['size']} bytes, params: {$m['parameters']})");
                        break;
                    }
                }
                if (!$found) {
                    $this->log("  Ollama model {$model} not found - pulling...");
                    @shell_exec("cd /d \"C:\\ollama\" && ollama pull {$model} 2>&1");
                }
            } else {
                $this->log("  Ollama not responding at {$baseUrl}");
            }
        } catch (\Exception $e) {
            $this->log("  Ollama check failed: " . $e->getMessage());
        }
    }

    private function reportProgress(): void
    {
        $state = $this->loadState();
        $state['last_cycle'] = $this->cycle;
        $state['last_run'] = date('Y-m-d H:i:s');
        $state['total_cycles'] = ($state['total_cycles'] ?? 0) + 1;

        $summary = sprintf(
            "[%s] Cycle %d | Tasks: %d | State: %s",
            date('Y-m-d H:i:s'),
            $this->cycle,
            count($this->loadTasks()),
            'running'
        );
        $this->log($summary);

        $this->saveState($state);
    }

    private function saveTasks(array $tasks): void
    {
        file_put_contents(
            __DIR__ . '/../state/tasks.json',
            json_encode(['tasks' => $tasks, 'updated' => date('Y-m-d H:i:s')], JSON_PRETTY_PRINT)
        );
    }

    private function loadTasks(): array
    {
        $file = __DIR__ . '/../state/tasks.json';
        if (file_exists($file)) {
            return json_decode(file_get_contents($file), true)['tasks'] ?? [];
        }
        return [];
    }

    private function loadState(): array
    {
        if (file_exists($this->stateFile)) {
            return json_decode(file_get_contents($this->stateFile), true) ?: [];
        }
        return [];
    }

    private function saveState(array $state = []): void
    {
        $state['project'] = $this->config['project'];
        $state['last_cycle'] = $this->cycle;
        $state['mode'] = $this->config['scheduler']['mode'];
        file_put_contents($this->stateFile, json_encode($state, JSON_PRETTY_PRINT));
    }

    private function log(string $message): void
    {
        $line = sprintf("[%s] %s\n", date('Y-m-d H:i:s'), $message);
        file_put_contents($this->logFile, $line, FILE_APPEND);
        echo $line;
    }
}

// Run if called directly
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($argv[0] ?? '')) {
    require __DIR__ . '/../config/bootstrap.php';
    $orc = new Orchestrator();
    $orc->run();
}