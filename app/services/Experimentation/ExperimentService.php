<?php

namespace App\Services\Experimentation;

use App\Core\Database\Database;
use PDO;
use Throwable;
use \App\Traits\ServiceTenantTrait;

/**
 * ExperimentService — A/B testing engine.
 *
 * Provides experiment lifecycle (create/run/end), deterministic variant
 * assignment via crc32 hash, event tracking, and statistical analysis
 * (per-variant conversion rates + 2-sample chi-square significance test).
 *
 * Variant assignment is consistent: the same (userId, experimentName) tuple
 * always returns the same variant — no DB state needed for assignment.
 *
 * Tables: ab_experiments, ab_events (created by scripts/add_ab_testing_tables.php)
 *
 * Usage:
 *   $svc = new ExperimentService();
 *   $svc->createExperiment('homepage_cta', [['name' => 'control', 'weight' => 50], ['name' => 'treatment', 'weight' => 50]]);
 *   $variant = $svc->getVariant('homepage_cta', $userId);   // 'control' or 'treatment' or null
 *   $svc->trackEvent('homepage_cta', $variant, 'view', $userId);
 *   $svc->trackEvent('homepage_cta', $variant, 'conversion', $userId);
 *   $stats = $svc->getStats('homepage_cta');
 */
class ExperimentService
{
    use ServiceTenantTrait;

    /** @var PDO */
    protected $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?: Database::getInstance()->getPdo();
    }

    // ───────────────────────────────────────────────────────────────────
    //   Experiment lifecycle
    // ───────────────────────────────────────────────────────────────────

    /**
     * Create a new experiment in 'running' state.
     *
     * @param string $name              Unique experiment name (slug-style)
     * @param array  $variants          [['name' => 'control', 'weight' => 50], ...]
     * @param int    $trafficAllocation 0-100, % of users included in experiment
     * @param string $description       Optional description
     * @return int                      Inserted experiment ID
     */
    public function createExperiment(string $name, array $variants, int $trafficAllocation = 100, string $description = ''): int
    {
        $this->validateVariants($variants);
        $trafficAllocation = max(0, min(100, $trafficAllocation));

        $sql = "INSERT INTO ab_experiments
                    (name, description, variants, traffic_allocation, status, started_at, created_at, tenant_id)
                VALUES (?, ?, ?, ?, 'running', NOW(), NOW(), ?)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $name,
            $description,
            json_encode(array_values($variants)),
            $trafficAllocation,
            $this->tenantId(),
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Get the assigned variant for a (userId, experimentName) tuple.
     *
     * Deterministic — same userId always gets the same variant (sticky bucketing).
     * Returns null if experiment isn't running OR the user is outside the
     * traffic_allocation cut.
     *
     * @return string|null Variant name (e.g. 'control', 'treatment') or null
     */
    public function getVariant(string $experimentName, int $userId): ?string
    {
        $exp = $this->getExperimentByName($experimentName);
        if (!$exp || $exp['status'] !== 'running') {
            return null;
        }

        // Deterministic bucketing seed: combine user + experiment
        $seed = $userId . ':' . $experimentName;
        $bucket = abs(crc32($seed)) % 100;

        // Outside traffic allocation? Not in the experiment.
        $traffic = (int) ($exp['traffic_allocation'] ?? 100);
        if ($bucket >= $traffic) {
            return null;
        }

        $variants = $this->decodeVariants($exp['variants']);
        if (empty($variants)) {
            return null;
        }

        // Weighted assignment by bucket: use crc32 of full seed for distribution
        // across variant weights — independent from the traffic-allocation check.
        $totalWeight = 0;
        foreach ($variants as $v) {
            $totalWeight += (int) ($v['weight'] ?? 1);
        }
        if ($totalWeight <= 0) {
            return null;
        }

        $vBucket = abs(crc32('variant:' . $seed)) % $totalWeight;
        $running = 0;
        foreach ($variants as $v) {
            $running += (int) ($v['weight'] ?? 1);
            if ($vBucket < $running) {
                return (string) $v['name'];
            }
        }

        return (string) $variants[0]['name'];
    }

    /**
     * Track an event (view, click, conversion, etc.) for an experiment.
     *
     * @param string $experimentName
     * @param string $variant
     * @param string $eventType       'view' | 'click' | 'conversion' | custom
     * @param int    $userId
     * @param array  $metadata        Optional key/value context
     * @return bool                   true on success
     */
    public function trackEvent(string $experimentName, string $variant, string $eventType, int $userId, array $metadata = []): bool
    {
        $exp = $this->getExperimentByName($experimentName);
        if (!$exp) {
            return false;
        }

        try {
            $sql = "INSERT INTO ab_events
                        (experiment_id, user_id, variant, event_type, metadata, tenant_id, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                (int) $exp['id'],
                $userId,
                $variant,
                $eventType,
                $metadata ? json_encode($metadata) : null,
                $this->tenantId(),
            ]);
            return true;
        } catch (Throwable $e) {
            error_log('ExperimentService::trackEvent failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * End an experiment and optionally declare a winner.
     */
    public function endExperiment(string $experimentName, ?string $winner = null): bool
    {
        $sql = "UPDATE ab_experiments
                   SET status = 'ended', ended_at = NOW(), winner = ?
                 WHERE name = ? AND tenant_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$winner, $experimentName, $this->tenantId()]);
    }

    /**
     * Delete an experiment and all its events.
     */
    public function deleteExperiment(int $experimentId): bool
    {
        try {
            $this->pdo->beginTransaction();
            $this->pdo->prepare("DELETE FROM ab_events WHERE experiment_id = ? AND tenant_id = ?")->execute([$experimentId, $this->tenantId()]);
            $this->pdo->prepare("DELETE FROM ab_experiments WHERE id = ? AND tenant_id = ?")->execute([$experimentId, $this->tenantId()]);
            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            error_log('ExperimentService::deleteExperiment failed: ' . $e->getMessage());
            return false;
        }
    }

    // ───────────────────────────────────────────────────────────────────
    //   Reporting & stats
    // ───────────────────────────────────────────────────────────────────

    /**
     * Per-variant results: distinct users, conversions, conversion-rate.
     *
     * @return array  ['variant_name' => ['users' => N, 'conversions' => M, 'rate' => 0.xx], ...]
     */
    public function getResults(string $experimentName): array
    {
        $exp = $this->getExperimentByName($experimentName);
        if (!$exp) return [];

        $variants = $this->decodeVariants($exp['variants']);
        $expId = (int) $exp['id'];
        $results = [];

        foreach ($variants as $v) {
            $variantName = (string) $v['name'];

            // Distinct users assigned to this variant (any event counts)
            $userStmt = $this->pdo->prepare(
                "SELECT COUNT(DISTINCT user_id) FROM ab_events
                  WHERE experiment_id = ? AND tenant_id = ? AND variant = ?"
            );
            $userStmt->execute([$expId, $this->tenantId(), $variantName]);
            $users = (int) $userStmt->fetchColumn();

            // Distinct conversions (user_id may convert multiple times — count once)
            $convStmt = $this->pdo->prepare(
                "SELECT COUNT(DISTINCT user_id) FROM ab_events
                  WHERE experiment_id = ? AND tenant_id = ? AND variant = ? AND event_type = 'conversion'"
            );
            $convStmt->execute([$expId, $this->tenantId(), $variantName]);
            $conversions = (int) $convStmt->fetchColumn();

            $rate = $users > 0 ? ($conversions / $users) : 0.0;

            $results[$variantName] = [
                'users'       => $users,
                'conversions' => $conversions,
                'rate'        => $rate,
                'rate_pct'    => round($rate * 100, 2),
            ];
        }

        return $results;
    }

    /**
     * Full stats payload (per-variant + totals + chi-square significance).
     *
     * @return array {
     *     experiment: array,
     *     results: array,
     *     totals: ['users' => N, 'conversions' => M],
     *     chi_square: ['stat' => x, 'p_value' => y, 'significant' => bool, 'df' => z]
     * }
     */
    public function getStats(string $experimentName): array
    {
        $exp = $this->getExperimentByName($experimentName);
        if (!$exp) {
            return ['error' => 'Experiment not found'];
        }

        $results = $this->getResults($experimentName);

        $totalUsers = 0;
        $totalConv = 0;
        foreach ($results as $r) {
            $totalUsers += $r['users'];
            $totalConv  += $r['conversions'];
        }

        return [
            'experiment' => [
                'id'           => (int) $exp['id'],
                'name'         => $exp['name'],
                'status'       => $exp['status'],
                'winner'       => $exp['winner'],
                'started_at'   => $exp['started_at'],
                'ended_at'     => $exp['ended_at'],
                'traffic_allocation' => (int) $exp['traffic_allocation'],
            ],
            'results'    => $results,
            'totals'     => [
                'users'       => $totalUsers,
                'conversions' => $totalConv,
                'rate'        => $totalUsers > 0 ? round(($totalConv / $totalUsers) * 100, 2) : 0,
            ],
            'chi_square' => $this->chiSquare($results),
        ];
    }

    /**
     * List all experiments (newest first).
     */
    public function listExperiments(): array
    {
$stmt = $this->pdo->prepare("SELECT id, name, description, variants, traffic_allocation, status, winner,
                    started_at, ended_at, created_at
               FROM ab_experiments
               WHERE tenant_id = ?
               ORDER BY created_at DESC");
        $stmt->execute([$this->tenantId()]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Augment with quick counts
        foreach ($rows as &$row) {
            $row['variants_decoded'] = $this->decodeVariants($row['variants']);
            $row['variant_count'] = count($row['variants_decoded']);

            $cnt = $this->pdo->prepare("SELECT COUNT(DISTINCT user_id) FROM ab_events WHERE experiment_id = ? AND tenant_id = ?");
            $cnt->execute([(int) $row['id'], $this->tenantId()]);
            $row['unique_users'] = (int) $cnt->fetchColumn();

            $cv = $this->pdo->prepare("SELECT COUNT(DISTINCT user_id) FROM ab_events WHERE experiment_id = ? AND tenant_id = ? AND event_type = 'conversion'");
            $cv->execute([(int) $row['id'], $this->tenantId()]);
            $row['total_conversions'] = (int) $cv->fetchColumn();
        }
        return $rows;
    }

    /**
     * Seed the 4 standard experiments. Idempotent — re-running won't
     * create duplicates (uses UNIQUE name constraint).
     *
     * @return array  ['created' => [name,…], 'skipped' => [name,…]]
     */
    public function seedDefaults(): array
    {
        $seeds = [
            [
                'name'        => 'homepage_cta',
                'description' => 'A/B test the home page hero CTA copy ("Browse Properties" vs "Find Your Dream Home").',
                'variants'    => [
                    ['name' => 'control',   'weight' => 50],
                    ['name' => 'treatment', 'weight' => 50],
                ],
                'traffic'     => 100,
            ],
            [
                'name'        => 'property_card_layout',
                'description' => 'Property listing card density: current (3 per row) vs compact (4 per row).',
                'variants'    => [
                    ['name' => 'current', 'weight' => 50],
                    ['name' => 'compact', 'weight' => 50],
                ],
                'traffic'     => 100,
            ],
            [
                'name'        => 'cta_button_color',
                'description' => 'Home page primary CTA color: blue vs green vs orange.',
                'variants'    => [
                    ['name' => 'blue',   'weight' => 34],
                    ['name' => 'green',  'weight' => 33],
                    ['name' => 'orange', 'weight' => 33],
                ],
                'traffic'     => 100,
            ],
            [
                'name'        => 'registration_form_length',
                'description' => 'Customer registration form length: full (all fields) vs minimal (name+email+phone, then step 2).',
                'variants'    => [
                    ['name' => 'full',    'weight' => 50],
                    ['name' => 'minimal', 'weight' => 50],
                ],
                'traffic'     => 100,
            ],
        ];

        $created = [];
        $skipped = [];
        foreach ($seeds as $s) {
            try {
                $this->createExperiment($s['name'], $s['variants'], $s['traffic'], $s['description']);
                $created[] = $s['name'];
            } catch (Throwable $e) {
                // Likely a duplicate (1062) — that's fine, treat as skipped.
                $skipped[] = $s['name'];
            }
        }
        return ['created' => $created, 'skipped' => $skipped];
    }

    /**
     * Get experiment by ID (raw row).
     */
    public function getExperimentById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM ab_experiments WHERE id = ? AND tenant_id = ?");
        $stmt->execute([$id, $this->tenantId()]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Get all running experiments (used by middleware to assign variants on every request).
     */
    public function getRunningExperiments(): array
    {
        $stmt = $this->pdo->prepare("SELECT name FROM ab_experiments WHERE status = 'running' AND tenant_id = ?");
        $stmt->execute([$this->tenantId()]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }

    /**
     * Expose the underlying PDO handle for callers that need to do
     * low-level queries (CSV export, ad-hoc updates, etc.).
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    // ───────────────────────────────────────────────────────────────────
    //   Internals
    // ───────────────────────────────────────────────────────────────────

    protected function getExperimentByName(string $name): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM ab_experiments WHERE name = ? AND tenant_id = ?");
        $stmt->execute([$name, $this->tenantId()]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    protected function decodeVariants($json): array
    {
        if (is_array($json)) return $json;
        if (!$json) return [];
        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }

    protected function validateVariants(array $variants): void
    {
        if (count($variants) < 2) {
            throw new \InvalidArgumentException('Experiment requires at least 2 variants');
        }
        foreach ($variants as $v) {
            if (!is_array($v) || empty($v['name'])) {
                throw new \InvalidArgumentException('Each variant must have a "name" key');
            }
        }
    }

    /**
     * Chi-square test for independence on a 2 x K contingency table.
     * H0: conversion rate is independent of variant assignment.
     *
     * Returns the test statistic, degrees of freedom, approximate p-value
     * (from chi-square CDF), and a boolean "significant" flag at p < 0.05.
     *
     * @return array
     */
    public function chiSquare(array $results): array
    {
        $k = count($results);
        if ($k < 2) {
            return ['stat' => 0.0, 'p_value' => 1.0, 'significant' => false, 'df' => 0, 'note' => 'Need 2+ variants'];
        }

        // Build 2 x K table: rows = [converted, not_converted], cols = variants
        $colTotals = [];
        $totalConv = 0;
        $totalNonConv = 0;
        foreach ($results as $name => $r) {
            $u = (int) $r['users'];
            $c = (int) $r['conversions'];
            $colTotals[$name] = $u;
            $totalConv += $c;
            $totalNonConv += ($u - $c);
        }
        $grandTotal = $totalConv + $totalNonConv;
        if ($grandTotal <= 0) {
            return ['stat' => 0.0, 'p_value' => 1.0, 'significant' => false, 'df' => $k - 1, 'note' => 'No data'];
        }

        $chi2 = 0.0;
        foreach ($results as $name => $r) {
            $u = (int) $r['users'];
            $c = (int) $r['conversions'];
            $nc = $u - $c;

            // Expected counts under independence
            $expConv = ($totalConv * $u) / $grandTotal;
            $expNonConv = ($totalNonConv * $u) / $grandTotal;

            if ($expConv > 0)    $chi2 += pow($c - $expConv, 2) / $expConv;
            if ($expNonConv > 0) $chi2 += pow($nc - $expNonConv, 2) / $expNonConv;
        }

        $df = $k - 1;
        $pValue = $this->chiSquarePValue($chi2, $df);

        return [
            'stat'        => round($chi2, 4),
            'df'          => $df,
            'p_value'     => round($pValue, 5),
            'significant' => $pValue < 0.05,
        ];
    }

    /**
     * Approximate chi-square p-value via the regularized upper incomplete
     * gamma function (Q(df/2, chi2/2)). Pure PHP — no extensions needed.
     */
    protected function chiSquarePValue(float $chi2, int $df): float
    {
        if ($df <= 0 || $chi2 <= 0) return 1.0;
        return $this->gammaQ($df / 2.0, $chi2 / 2.0);
    }

    /**
     * Regularized upper incomplete gamma function Q(a, x) = 1 - P(a, x).
     */
    protected function gammaQ(float $a, float $x): float
    {
        if ($x < 0 || $a <= 0) return 1.0;
        if ($x < $a + 1) {
            return 1.0 - $this->gammaSeriesP($a, $x);
        }
        return $this->gammaContinuedFractionQ($a, $x);
    }

    protected function gammaSeriesP(float $a, float $x, int $maxIter = 200, float $eps = 1e-12): float
    {
        if ($x <= 0) return 0.0;
        $ap = $a;
        $sum = 1.0 / $a;
        $del = $sum;
        for ($n = 1; $n <= $maxIter; $n++) {
            $ap++;
            $del *= $x / $ap;
            $sum += $del;
            if (abs($del) < abs($sum) * $eps) break;
        }
        return $sum * exp(-$x + $a * log($x) - $this->logGamma($a));
    }

    protected function gammaContinuedFractionQ(float $a, float $x, int $maxIter = 200, float $eps = 1e-12): float
    {
        $b = $x + 1.0 - $a;
        $c = 1.0 / 1e-30;
        $d = 1.0 / $b;
        $h = $d;
        for ($i = 1; $i <= $maxIter; $i++) {
            $an = -$i * ($i - $a);
            $b += 2.0;
            $d = $an * $d + $b;
            if (abs($d) < 1e-30) $d = 1e-30;
            $c = $b + $an / $c;
            if (abs($c) < 1e-30) $c = 1e-30;
            $d = 1.0 / $d;
            $del = $d * $c;
            $h *= $del;
            if (abs($del - 1.0) < $eps) break;
        }
        return exp(-$x + $a * log($x) - $this->logGamma($a)) * $h;
    }

    /** Lanczos approximation for log Γ(z) (sufficient precision for chi2 p-values). */
    protected function logGamma(float $z): float
    {
        static $g = 7;
        static $coef = [
            0.99999999999980993, 676.5203681218851, -1259.1392167224028,
            771.32342877765313, -176.61502916214059, 12.507343278686905,
            -0.13857109526572012, 9.9843695780195716e-6, 1.5056327351493116e-7,
        ];
        if ($z < 0.5) {
            return log(M_PI / sin(M_PI * $z)) - $this->logGamma(1 - $z);
        }
        $z -= 1;
        $x = $coef[0];
        for ($i = 1; $i < $g + 2; $i++) {
            $x += $coef[$i] / ($z + $i);
        }
        $t = $z + $g + 0.5;
        return 0.5 * log(2 * M_PI) + ($z + 0.5) * log($t) - $t + log($x);
    }
}
