<?php
/**
 * CodeReviewAgent — senior-dev static review + production-log analyst (Session 306).
 *
 * Two halves, one table (`ai_code_findings`):
 *  1. Deterministic detectors over PHP sources (no AI needed, no network):
 *     missing-use-exception, superglobal-in-SQL-string, extract-in-render,
 *     BOM-prefixed PHP file. Each is a bug family this repo has actually paid
 *     for (see lessons _435, _491, _514, _595, S267-BOM).
 *  2. Production-log analyst: tails the PHP error log, groups fatals/warnings,
 *     files one finding per signature (dedupe: same rule+file+message open = skip).
 *  3. Optional AI explanation pass via FreeAIEngines (best-effort, never throws,
 *     skipped when no provider key is configured — static hits stand alone).
 *
 * Cost: ₹0. Writes only to `ai_code_findings` (+ `ai_alerts` for criticals).
 */

namespace App\Services\AI;

class CodeReviewAgent
{
    use \App\Traits\ServiceTenantTrait;

    private $db;

    /** Max files per scan + max bytes per file (cron safety). */
    private const MAX_FILES = 400;
    private const MAX_BYTES = 512000;

    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance();
    }

    /**
     * Run deterministic detectors over PHP source roots.
     * @return array findings (not persisted unless $store=true)
     */
    public function scan(array $roots = [], bool $store = false, ?string $runId = null): array
    {
        if (empty($roots)) {
            $base = defined('APP_ROOT') ? APP_ROOT . '/app' : __DIR__ . '/../..';
            $roots = [$base . '/Http/Controllers', $base . '/Services', $base . '/Core'];
        }
        $files = $this->collectPhpFiles($roots);
        $findings = [];
        foreach ($files as $file) {
            foreach ($this->detectInFile($file) as $hit) {
                $hit['run_id'] = $runId;
                $findings[] = $hit;
                if ($store) $this->storeFinding($hit);
            }
        }
        return $findings;
    }

    /**
     * Tail the PHP error log, group by signature, file one finding per group.
     */
    public function analyzeProductionLog(?string $logPath = null, int $tailLines = 600, bool $store = true, ?string $runId = null): array
    {
        $logPath = $logPath ?: 'C:\\xampp\\php\\logs\\php_error_log';
        if (!is_readable($logPath)) {
            return ['ok' => false, 'error' => 'log-unreadable', 'groups' => []];
        }
        $lines = $this->tailFile($logPath, $tailLines);
        $groups = [];
        foreach ($lines as $line) {
            if (!preg_match('/(Fatal error|Uncaught|Warning|Parse error|Allowed memory|Maximum execution time)/i', $line)) continue;
            $sig = $this->signatureOf($line);
            if (!isset($groups[$sig])) $groups[$sig] = ['count' => 0, 'sample' => trim(substr($line, 0, 400))];
            $groups[$sig]['count']++;
        }
        arsort($groups);
        $filed = 0;
        $out = [];
        foreach (array_slice($groups, 0, 20, true) as $sig => $g) {
            $finding = [
                'rule' => 'prod-log-group',
                'severity' => stripos($g['sample'], 'fatal') !== false || stripos($g['sample'], 'uncaught') !== false ? 'high' : 'medium',
                'file' => $this->extractPath($g['sample']) ?: '(log only)',
                'line' => null,
                'message' => "×{$g['count']} in last {$tailLines} lines: {$g['sample']}",
                'suggestion' => null,
                'source' => 'prod_log',
                'run_id' => $runId,
            ];
            $out[] = $finding + ['occurrences' => $g['count']];
            if ($store && $this->storeFinding($finding)) $filed++;
        }
        return ['ok' => true, 'groups' => count($groups), 'filed' => $filed, 'top' => array_slice($out, 0, 5)];
    }

    /**
     * Ask FreeAIEngines to explain a finding + suggest a fix. Best-effort.
     */
    public function aiExplain(array $finding): array
    {
        try {
            if (!class_exists(\App\Services\AI\FreeAIEngines::class)) return ['ok' => false, 'error' => 'no-engine'];
            $ai = \App\Services\AI\FreeAIEngines::getInstance();
            $prompt = "Code review finding [{$finding['rule']}] in {$finding['file']}"
                . (isset($finding['line']) ? " line {$finding['line']}" : '') . ":\n"
                . $finding['message'] . "\nExplain the bug in 2 sentences and give the minimal fix.";
            $res = $ai->generate($prompt, ['max_tokens' => 400, 'temperature' => 0.2], 'analyze');
            if (empty($res['text'])) return ['ok' => false, 'error' => 'empty-response'];
            return ['ok' => true, 'explanation' => $res['text'], 'engine' => $res['engine'] ?? 'unknown'];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => substr($e->getMessage(), 0, 200)];
        }
    }

    /* ---------------- detectors ---------------- */

    private function detectInFile(string $file): array
    {
        $raw = @file_get_contents($file);
        if ($raw === false || strlen($raw) > self::MAX_BYTES) return [];
        $findings = [];
        $lines = explode("\n", $raw);

        // D0: BOM-prefixed PHP (S267/S432 family — breaks JSON globally)
        if (substr($raw, 0, 3) === "\xEF\xBB\xBF") {
            $findings[] = $this->hit($file, 1, 'bom-prefix', 'high', 'UTF-8 BOM at byte 0 — prefixes every response that includes this file.');
        }
        $hasUseException = false;
        foreach ($lines as $i => $ln) {
            if (preg_match('/^\s*use\s+[^;]*\bException\b\s*;/', $ln)) { $hasUseException = true; break; }
        }
        foreach ($lines as $i => $ln) {
            $n = $i + 1;
            $t = trim($ln);
            if ($t === '' || strpos($t, '//') === 0 || strpos($t, '*') === 0) continue;

            // D1: catch (Exception without `use Exception` in a namespaced file (S595 family)
            if (!$hasUseException && preg_match('/catch\s*\(\s*(\\\?Exception)\b/', $ln)
                && preg_match('/^namespace\s/m', $raw)) {
                $findings[] = $this->hit($file, $n, 'missing-use-exception', 'high', 'catch( Exception ) with no `use Exception` — resolves per-namespace and silently misses; the catch never fires.');
                $hasUseException = true; // report once per file
            }

            // D2: superglobal interpolated into a SQL string (S263 family)
            if (preg_match('/\b(SELECT|INSERT|UPDATE|DELETE)\b/i', $ln)
                && preg_match('/\.\s*\$_(GET|POST|REQUEST|COOKIE)\b|\$_(GET|POST|REQUEST|COOKIE)\b\s*\./', $ln)
                && strpos($ln, '?') === false) {
                $findings[] = $this->hit($file, $n, 'superglobal-in-sql', 'critical', 'Superglobal concatenated into SQL with no placeholder — injection-shaped. Bind it.');
            }

            // D3: extract() in render/controller paths (S491 family)
            if (preg_match('/\bextract\s*\(/', $ln)
                && preg_match('/render|view|template|layout/i', $file . ' ' . $ln)) {
                $findings[] = $this->hit($file, $n, 'extract-in-render', 'medium', 'extract() can clobber same-named locals (e.g. $view). Prefer explicit assignment.');
            }
        }
        return $findings;
    }

    private function hit(string $file, ?int $line, string $rule, string $severity, string $message): array
    {
        return [
            'rule' => $rule, 'severity' => $severity, 'file' => $file, 'line' => $line,
            'message' => $message, 'suggestion' => null, 'source' => 'static_scan', 'run_id' => null,
        ];
    }

    /* ---------------- storage ---------------- */

    private function storeFinding(array $f): bool
    {
        try {
            $tid = $this->tenantId();
            // dedupe: same rule+file+message already open = skip
            $dup = $this->db->fetchOne(
                "SELECT id FROM ai_code_findings WHERE `rule` = ? AND `file` = ? AND `message` = ? AND status = 'open'" . ($tid > 1 ? " AND tenant_id = ?" : "") . " LIMIT 1",
                array_merge([$f['rule'], $f['file'], $f['message']], $tid > 1 ? [$tid] : [])
            );
            if ($dup) return false;
            $cols = "`rule`, `severity`, `file`, `line`, `message`, `suggestion`, `status`, `source`, `run_id`, `tenant_id`";
            $this->db->execute(
                "INSERT INTO ai_code_findings ($cols) VALUES (?, ?, ?, ?, ?, ?, 'open', ?, ?, ?)",
                [$f['rule'], $f['severity'], $f['file'], $f['line'], $f['message'], $f['suggestion'] ?? null, $f['source'] ?? 'static_scan', $f['run_id'] ?? null, $tid > 1 ? $tid : 1]
            );
            return true;
        } catch (\Throwable $e) {
            error_log('CodeReviewAgent::storeFinding: ' . $e->getMessage());
            return false;
        }
    }

    /* ---------------- helpers ---------------- */

    private function collectPhpFiles(array $roots): array
    {
        $out = [];
        foreach ($roots as $root) {
            if (!is_dir($root)) continue;
            $it = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS)
            );
            foreach ($it as $f) {
                if (!$f->isFile() || $f->getExtension() !== 'php') continue;
                $p = $f->getPathname();
                if (stripos($p, 'vendor') !== false || stripos($p, '_archive') !== false) continue;
                $out[] = $p;
                if (count($out) >= self::MAX_FILES) break 2;
            }
        }
        return $out;
    }

    private function tailFile(string $path, int $lines): array
    {
        $fh = @fopen($path, 'rb');
        if (!$fh) return [];
        $pos = -1;
        $out = [];
        $buf = '';
        fseek($fh, 0, SEEK_END);
        $end = ftell($fh);
        $chunk = 8192;
        while ($end > 0 && count($out) < $lines) {
            $read = min($chunk, $end);
            $end -= $read;
            fseek($fh, $end);
            $buf = fread($fh, $read) . $buf;
            $parts = explode("\n", $buf);
            $buf = array_shift($parts);
            $out = array_merge($parts, $out);
            if (count($out) >= $lines) { $out = array_slice($out, -$lines); break; }
        }
        fclose($fh);
        return $out;
    }

    private function signatureOf(string $line): string
    {
        // strip timestamps, pids, memory sizes, paths→basename
        $s = preg_replace('/^\[.*?\]\s*/', '', $line);
        $s = preg_replace('/pid \d+|tid \d+|#\d+/', '#N', $s);
        $s = preg_replace('/\d+(\.\d+)?\s?(MB|KB|bytes|ms|s)/i', 'N', $s);
        $s = preg_replace('/[A-Z]:\\\\[^\s:]*/', 'PATH', $s);
        $s = preg_replace('/\/[^\s:]*/', 'PATH', $s);
        $s = preg_replace('/line \d+/i', 'line N', $s);
        $s = preg_replace('/\s+/', ' ', trim($s));
        return substr($s, 0, 300);
    }

    private function extractPath(string $sample): ?string
    {
        if (preg_match('/([A-Z]:\\\\[^\s:]+\.php)/', $sample, $m)) return $m[1];
        if (preg_match('/(\/[^\s:]+\.php)/', $sample, $m)) return $m[1];
        return null;
    }

    private function tenantId(): int
    {
        try { return (int)\App\Core\Middleware\TenantContext::getId(); }
        catch (\Throwable $e) { return 1; }
    }
}
