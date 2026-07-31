<?php

namespace App\Core\Agentic;

class TaskDiscovery
{
    private array $projectRoot;
    private array $tasks = [];

    public function __construct(string $projectRoot)
    {
        $this->projectRoot = $projectRoot;
    }

    public function discover(): array
    {
        $this->tasks = [];
        $this->discoverFromGit();
        $this->discoverFromPhpSyntax();
        $this->discoverFromAgentsMd();
        $this->discoverFromE2E();
        $this->discoverFromArchive();
        return $this->tasks;
    }

    private function discoverFromGit(): void
    {
        $diff = @shell_exec('cd /d "' . $this->projectRoot . '" && git diff --stat HEAD~5 2>&1');
        if (!empty(trim($diff ?? ''))) {
            $this->tasks[] = [
                'type' => 'git_changes',
                'priority' => 'high',
                'desc' => 'Recent code changes detected',
                'detail' => trim(substr($diff, 0, 500))
            ];
        }
    }

    private function discoverFromPhpSyntax(): void
    {
        $result = @shell_exec(
            'cd /d "' . $this->projectRoot . '" && ' .
            'find app/Http/Controllers app/Services -name "*.php" -exec php -l {} \; 2>&1 | ' .
            'findstr /i "error" | findstr /v "No syntax errors"'
        );
        $errors = array_filter(array_map('trim', explode("\n", $result ?? '')));
        if (!empty($errors)) {
            $this->tasks[] = [
                'type' => 'php_syntax',
                'priority' => 'critical',
                'desc' => count($errors) . ' PHP syntax errors found',
                'detail' => implode("\n", array_slice($errors, 0, 10))
            ];
        }
    }

    private function discoverFromAgentsMd(): void
    {
        $agentsMd = @file_get_contents($this->projectRoot . '/AGENTS.md');
        if ($agentsMd === false) return;

        preg_match_all('/\| P(\d+) \| \*\*([^*]+)\*\* \| (.+?) \|/s', $agentsMd, $matches);
        if (!empty($matches[2])) {
            foreach ($matches[2] as $i => $task) {
                $status = $matches[3][$i] ?? '';
                if (strpos($status, 'COMPLETE') === false && strpos($status, 'Done') === false) {
                    $this->tasks[] = [
                        'type' => 'pending_agent_task',
                        'priority' => 'medium',
                        'desc' => trim($task),
                        'detail' => 'See AGENTS.md for details'
                    ];
                }
            }
        }
    }

    private function discoverFromE2E(): void
    {
        $lastResult = @file_get_contents($this->projectRoot . '/testing/visual_tests/.last_result.json');
        if ($lastResult === false) {
            $this->tasks[] = [
                'type' => 'e2e_not_run',
                'priority' => 'high',
                'desc' => 'E2E tests have not been run recently',
                'detail' => 'Run node testing/visual_tests/E2E_MASTER_TEST.mjs'
            ];
            return;
        }

        $result = json_decode($lastResult, true);
        if ($result && ($result['rate'] ?? 1) < 0.99) {
            $this->tasks[] = [
                'type' => 'e2e_failures',
                'priority' => 'critical',
                'desc' => sprintf('E2E test failure: %d/%d pass', $result['pass'] ?? 0, $result['total'] ?? 0),
                'detail' => 'Check testing/visual_tests/ for details'
            ];
        }
    }

    private function discoverFromArchive(): void
    {
        $archiveDir = $this->projectRoot . '/_archive';
        if (!is_dir($archiveDir)) return;

        $count = 0;
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($archiveDir));
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $count++;
            }
        }
        if ($count > 100) {
            $this->tasks[] = [
                'type' => 'archive_growth',
                'priority' => 'low',
                'desc' => "{$count} files in _archive - consider periodic audit",
                'detail' => 'Review _archive for safe cleanup every few sessions'
            ];
        }
    }
}