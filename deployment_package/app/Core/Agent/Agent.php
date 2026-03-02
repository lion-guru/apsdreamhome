<?php

namespace App\Core\Agent;

use App\Core\AI\OpenRouterClient;

class Agent {
    public function runDailyOps(): array {
        $summary = $this->collectSummary();
        $client = new OpenRouterClient();
        $system = 'You are an executive AI agent (CEO/CTO) for an Indian real estate platform. Plan actions to improve growth, operations, and product across frontend, backend, CRM, and marketing. Output concise bullet steps.';
        $user = $summary;
        $result = $client->chat($system, $user);
        $content = $result['ok'] ? $result['content'] : $this->fallbackPlan($summary);
        $this->log($content);
        return ['planned' => $content, 'online' => $result['ok']];
    }
    public function generateReport(): array {
        $summary = $this->collectSummary();
        $content = "Report\n" . $summary;
        $this->log($content);
        return ['report' => $content];
    }
    private function collectSummary(): string {
        $stats = $this->dbStats();
        $lines = [];
        $lines[] = 'Key metrics:';
        foreach ($stats as $k => $v) $lines[] = $k . ': ' . $v;
        $lines[] = 'Objectives: security hardening, routing/view consolidation, logging, performance, tests, growth.';
        return implode("\n", $lines);
    }
    private function dbStats(): array {
        $conn = $this->getMysqli();
        if (!$conn) return ['tables' => 'unknown', 'leads' => 0, 'properties' => 0, 'projects' => 0, 'users' => 0];
        $tables = 0;
        $r = $conn->query('SHOW TABLES');
        if ($r) $tables = $r->num_rows;
        $leads = $this->count($conn, 'leads');
        $props = $this->count($conn, 'properties');
        $projs = $this->count($conn, 'projects');
        $users = $this->count($conn, 'users');
        return ['tables' => $tables, 'leads' => $leads, 'properties' => $props, 'projects' => $projs, 'users' => $users];
    }
    private function count($conn, string $table): int {
        $q = $conn->query('SELECT COUNT(*) c FROM `' . $table . '`');
        if ($q && ($row = $q->fetch_assoc())) return (int) $row['c'];
        return 0;
    }
    private function getMysqli() {
        if (!function_exists('config')) {
            require_once dirname(__DIR__, 3) . '/includes/config.php';
        }
        return \AppConfig::getInstance()->getDatabaseConnection();
    }
    private function fallbackPlan(string $summary): string {
        $steps = [
            '- Enforce CSRF and sanitize uploads',
            '- Unify router and migrate homepage to consolidated view',
            '- Create admin observability (errors, slow queries)',
            '- Add indexes for leads/properties/projects',
            '- Clean vendor assets and run Lighthouse',
            '- Expand tests and enable CI gates',
            '- Launch targeted campaigns and improve saved searches',
        ];
        return implode("\n", $steps) . "\n" . $summary;
    }
    private function log(string $text): void {
        $dir = dirname(__DIR__, 3) . '/storage/logs';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        $line = '[' . date('Y-m-d H:i:s') . '] ' . str_replace(["\r", "\n"], ' ', $text) . "\n";
        file_put_contents($dir . '/agent.log', $line, FILE_APPEND | LOCK_EX);
    }
}

