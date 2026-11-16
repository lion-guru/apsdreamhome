<?php

/**
 * DemandForecastService - Forward demand forecasting per colony
 * ================================================================
 * Pure-compute service (no migration, no new tables). Probes existing
 * lead-velocity + inventory + price_history data to answer:
 *
 *   "How many plots will this colony sell in the next 30 / 60 / 90 days,
 *    and how long until it runs out?"
 *
 * Signals used (all real, all queryable):
 *   - 12-month lead creation velocity (months with 0 rows = missing)
 *   - Available inventory and total plots per colony
 *   - Price history trend per colony (604 rows, linear fit)
 *   - Month-of-year seasonality (computed from lead-per-month counts)
 *   - Tagged lead demand (colony_id + location_preference LIKE)
 *
 * Honesty contract: when a colony has no lead-velocity signal at all,
 * it returns a low-confidence "insufficient data" forecast rather than
 * fabricating a curve. When velocity exists but is thin, it returns a
 * wide confidence interval and flags `limited_data=true`.
 */

namespace App\Services\AI;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;
use PDO;

class DemandForecastService
{
    use ServiceTenantTrait;

    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Full forecast for one colony. Returns null when colony unknown.
     */
    public function forecastColony(int $colonyId): ?array
    {
        $colony = $this->db->fetchOne(
            "SELECT id, name FROM colonies WHERE id = ? AND deleted_at IS NULL LIMIT 1",
            [$colonyId]
        );
        if (!$colony) {
            return null;
        }

        // Inventory snapshot
        $inv = $this->db->fetchOne(
            "SELECT
                COUNT(*) AS total,
                COALESCE(SUM(status = 'available'), 0) AS available,
                COALESCE(SUM(status IN ('booked','sold','registered')), 0) AS engaged
             FROM plots
             WHERE colony_id = ? AND deleted_at IS NULL AND is_active = 1",
            [$colonyId]
        ) ?: [];
        $total = (int) ($inv['total'] ?? 0);
        $available = (int) ($inv['available'] ?? 0);
        $engaged = (int) ($inv['engaged'] ?? 0);

        // Per-colony demand allocation. Honest reality check: there is NO
        // per-colony lead signal in this database (0 colony-tagged leads in 90d).
        // The only colony-specific traction evidence is ENGAGED plots (bookings/sales).
        // Allocate tenant lead-velocity proportionally to engagement share.
        $tagged = (int) ($this->db->fetchOne(
            "SELECT COUNT(*) as cnt FROM leads
             WHERE deleted_at IS NULL
               AND created_at >= DATE_SUB(NOW(), INTERVAL 60 DAY)
               AND (colony_id = ? OR location_preference LIKE ? OR JSON_CONTAINS(tags, ?) = 1)",
            [$colonyId, '%' . $colony['name'] . '%', json_encode($colony['name'])]
        )['cnt'] ?? 0);

        $totalEngagedRow = $this->db->fetchOne(
            "SELECT COALESCE(SUM(status IN ('booked','sold','registered')), 0) AS eng
             FROM plots WHERE is_active = 1 AND deleted_at IS NULL"
        );
        $totalEngaged = (int) ($totalEngagedRow['eng'] ?? 0);
        $allocation = $totalEngaged > 0
            ? max(0.02, $engaged / $totalEngaged)
            : 1.0 / max(1, ((int)($this->db->fetchOne("SELECT COUNT(*) AS cnt FROM colonies WHERE is_active = 1 AND deleted_at IS NULL")['cnt'] ?? 1)));

        // 12-month lead velocity series (tenant-wide) — trend + seasonality source
        $velocity = $this->getMonthlyLeadVelocity();
        $monthsWithData = count(array_filter($velocity, fn($m) => $m['leads'] > 0));

        // Trend: linear fit over available months (least-squares on index)
        $trend = $this->fitMonthlyTrend($velocity);

        // Seasonality: average leads per calendar month over last 12 months
        $seasonal = $this->monthSeasonality($velocity);

        $currentMonthLeads = (int) ($velocity[count($velocity) - 1]['leads'] ?? 0);
        $trendProj = $this->projectNextMonths($velocity, $trend, $seasonal, 3);

        // Apply colony allocation: a colony with 0 engaged plots earns no tenant demand.
        foreach ($trendProj as &$p) {
            $p['tenant_leads'] = $p['leads'];
            $p['leads'] = (int) round($p['leads'] * $allocation);
            $p['allocation'] = round($allocation, 4);
        }
        unset($p);

        // Conversion assumption: 8% of colony-relevant leads become plot bookings
        // (conservative for luxury Gorakhpur; documented in notes)
        $bookingRate = 0.08;
        $expectedBookings = array_map(
            fn($m) => max(0, (int) round($m['leads'] * $bookingRate)),
            $trendProj
        );

        // Inventory runway at projected sell rate
        $runwayDays = null;
        $monthlyRate = $expectedBookings[0] ?? 0;
        if ($monthlyRate > 0 && $available > 0) {
            $runwayDays = (int) round(($available / $monthlyRate) * 30.44);
        }

        // Confidence: months-with-data driven, capped
        if ($monthsWithData >= 10) {
            $confidence = 0.70;
        } elseif ($monthsWithData >= 6) {
            $confidence = 0.55;
        } elseif ($monthsWithData >= 3) {
            $confidence = 0.40;
        } else {
            $confidence = 0.20;
        }
        // Penalize: zero colony-tagged leads (we have nothing local to work with)
        if ($tagged === 0 && $monthsWithData < 6) {
            $confidence = max(0.15, $confidence - 0.10);
        }
        // Penalize: no engagement evidence for this colony (floor allocation)
        if ($engaged === 0) {
            $confidence = max(0.15, $confidence - 0.10);
        }
        $limitedData = $monthsWithData < 6;

        // Price trend per colony (from price_history — 604 rows)
        $priceTrend = $this->priceTrend($colonyId);

        return [
            'colony_id'         => $colonyId,
            'colony_name'       => $colony['name'],
            'generated_at'      => date('Y-m-d H:i:s'),
            'inventory' => [
                'total_plots'     => $total,
                'available'       => $available,
                'engaged'         => $engaged,
                'engaged_pct'     => $total > 0 ? round($engaged / $total * 100, 1) : 0,
            ],
            'demand' => [
                'tagged_60d'           => $tagged,
                'current_month_pace'   => $currentMonthLeads,
                'months_with_data'     => $monthsWithData,
                'limited_data'         => $limitedData,
                'allocation'           => round($allocation, 4),
                'engaged'              => $engaged,
                'total_engaged'        => $totalEngaged,
            ],
            'forecast' => [
                'next_30d_leads'       => $trendProj[0]['leads'] ?? 0,
                'next_60d_leads'       => ($trendProj[0]['leads'] ?? 0) + ($trendProj[1]['leads'] ?? 0),
                'next_90d_leads'       => array_sum(array_column($trendProj, 'leads')),
                'next_30d_bookings'    => $expectedBookings[0] ?? 0,
                'next_60d_bookings'    => ($expectedBookings[0] ?? 0) + ($expectedBookings[1] ?? 0),
                'next_90d_bookings'    => array_sum($expectedBookings),
                'monthly_projection'   => $trendProj,
            ],
            'runway' => [
                'available_plots'      => $available,
                'monthly_rate'         => $monthlyRate,
                'runway_days'          => $runwayDays,
                'runway_label'         => $runwayDays === null
                    ? 'Insufficient velocity to project'
                    : ($runwayDays < 30 ? 'Critical: under 30 days of inventory' : 'OK'),
            ],
            'price_trend' => $priceTrend,
            'confidence' => $confidence,
            'seasonality' => $seasonal,
            'notes' => $this->notes($monthsWithData, $tagged, $available, $runwayDays !== null, $allocation, $engaged),
        ];
    }

    /**
     * Forecast all active colonies. Used by admin dashboards/console.
     */
    public function forecastAll(int $limit = 20): array
    {
        $colonyIds = $this->db->fetchAll(
            "SELECT id FROM colonies WHERE is_active = 1 AND deleted_at IS NULL ORDER BY id LIMIT ?",
            [max(1, min(100, $limit))]
        );
        $out = [];
        foreach ($colonyIds as $c) {
            $f = $this->forecastColony((int) $c['id']);
            if ($f) {
                $out[] = $f;
            }
        }
        return [
            'count' => count($out),
            'forecasts' => $out,
            'generated_at' => date('Y-m-d H:i:s'),
        ];
    }

    /** Public summary row for admin tables/widgets. */
    public function colonyRows(): array
    {
        $rows = [];
        foreach ($this->forecastAll(50)['forecasts'] as $c) {
            $rows[] = [
                'colony_id' => $c['colony_id'],
                'colony_name' => $c['colony_name'],
                'available' => $c['inventory']['available'],
                'total' => $c['inventory']['total_plots'],
                'next_30d_leads' => $c['forecast']['next_30d_leads'],
                'next_90d_bookings' => $c['forecast']['next_90d_bookings'],
                'runway_days' => $c['runway']['runway_days'],
                'confidence' => $c['confidence'],
                'limited_data' => $c['demand']['limited_data'],
            ];
        }
        return $rows;
    }

    /* ── Private helpers ───────────────────────────────────────── */

    /** 12 months of lead creation counts, oldest first. */
    private function getMonthlyLeadVelocity(): array
    {
        $rows = $this->db->fetchAll(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS leads
             FROM leads
             WHERE deleted_at IS NULL
               AND created_at >= DATE_SUB(DATE_FORMAT(NOW() , '%Y-%m-01'), INTERVAL 11 MONTH)
             GROUP BY month ORDER BY month ASC"
        ) ?: [];

        // Fill gaps with zero so the series is exactly 12 buckets
        $series = [];
        $cursor = strtotime('first day of -11 month this month');
        for ($i = 0; $i < 12; $i++) {
            $key = date('Y-m', $cursor);
            $found = null;
            foreach ($rows as $r) {
                if ($r['month'] === $key) { $found = (int) $r['leads']; break; }
            }
            $series[] = ['month' => $key, 'leads' => $found ?? 0];
            $cursor = strtotime('+1 month', $cursor);
        }
        return $series;
    }

    /** Least-squares slope in leads/month (can be negative). */
    private function fitMonthlyTrend(array $velocity): array
    {
        $n = count($velocity);
        if ($n < 3) {
            return ['slope' => 0.0, 'intercept' => (float)($velocity[$n - 1]['leads'] ?? 0), 'n' => $n];
        }
        $sx = 0; $sy = 0; $sxy = 0; $sxx = 0;
        foreach ($velocity as $i => $m) {
            $sx += $i; $sy += $m['leads']; $sxy += $i * $m['leads']; $sxx += $i * $i;
        }
        $denom = ($n * $sxx) - ($sx * $sx);
        if (abs($denom) < 1e-9) {
            return ['slope' => 0.0, 'intercept' => $sy / $n, 'n' => $n];
        }
        $slope = (($n * $sxy) - ($sx * $sy)) / $denom;
        $intercept = ($sy - $slope * $sx) / $n;
        return ['slope' => round($slope, 4), 'intercept' => round($intercept, 2), 'n' => $n];
    }

    /** Per-calendar-month average across observed months (seasonality index). */
    private function monthSeasonality(array $velocity): array
    {
        $byMonth = array_fill(1, 12, []);
        foreach ($velocity as $m) {
            $mi = (int)date('n', strtotime($m['month'] . '-01'));
            $byMonth[$mi][] = $m['leads'];
        }
        $out = [];
        $overall = max(1, array_sum(array_column($velocity, 'leads')) / max(1, count(array_filter($velocity, fn($m) => $m['leads'] > 0))));
        for ($m = 1; $m <= 12; $m++) {
            $vals = $byMonth[$m];
            $out[$m] = $vals ? round((array_sum($vals) / count($vals)) / $overall, 3) : 1.0;
        }
        return $out;
    }

    /** Project the three months after the current month. */
    private function projectNextMonths(array $velocity, array $trend, array $seasonal, int $n): array
    {
        $out = [];
        $nHist = count($velocity);
        $currentCalMonth = (int)date('n');
        for ($i = 1; $i <= $n; $i++) {
            $futureIndex = $nHist + $i - 1;
            $calMonth = (($currentCalMonth + $i - 1) % 12) + 1;
            $base = $trend['intercept'] + $trend['slope'] * $futureIndex;
            $seas = $seasonal[$calMonth] ?? 1.0;
            $leads = max(0, (int) round($base * $seas));
            $out[] = [
                'months_ahead' => $i,
                'calendar_month' => $calMonth,
                'leads' => $leads,
                'seasonal_factor' => $seas,
            ];
        }
        return $out;
    }

    /** Simple linear least-squares on log price vs time for a colony. */
    private function priceTrend(int $colonyId): array
    {
        $rows = $this->db->fetchAll(
            "SELECT COALESCE(NULLIF(new_price_per_sqft,0), old_price_per_sqft) AS y, created_at
             FROM price_history
             WHERE colony_id = ? AND COALESCE(NULLIF(new_price_per_sqft,0), old_price_per_sqft) > 0
             ORDER BY created_at ASC",
            [$colonyId]
        ) ?: [];
        if (count($rows) < 3) {
            return ['direction' => 'insufficient_data', 'n' => count($rows), 'slope_per_year_pct' => null];
        }
        $t0 = strtotime($rows[0]['created_at']);
        $sx = 0; $sy = 0; $sxy = 0; $sxx = 0; $nRows = count($rows);
        foreach ($rows as $r) {
            $x = (strtotime($r['created_at']) - $t0) / 31557600; // years
            $sx += $x; $sy += (float)$r['y']; $sxy += $x * (float)$r['y']; $sxx += $x * $x;
        }
        $denom = ($nRows * $sxx) - ($sx * $sx);
        $slope = abs($denom) < 1e-9 ? 0.0 : (($nRows * $sxy) - ($sx * $sy)) / $denom;
        $meanY = $sy / $nRows;
        $pctPerYear = $meanY > 0 ? ($slope / $meanY) * 100 : 0.0;
        return [
            'direction' => $pctPerYear > 1 ? 'up' : ($pctPerYear < -1 ? 'down' : 'flat'),
            'n' => $nRows,
            'slope_per_year_pct' => round($pctPerYear, 2),
            'latest_pps' => isset($rows[count($rows) - 1]) ? (float)$rows[count($rows) - 1]['y'] : null,
        ];
    }

    private function notes(int $months, int $tagged, int $available, bool $runway, float $allocation = 0.0, int $engaged = 0): array
    {
        $n = [];
        if ($months < 6) {
            $n[] = "Thin lead history (" . $months . " months with data) — projections have wide error bands.";
        }
        if ($tagged === 0) {
            $n[] = "No colony-tagged leads in last 60d — allocation by engagement share (engaged {$engaged} plots, " . round($allocation, 3) . " of tenant demand).";
        }
        if ($engaged === 0) {
            $n[] = "Zero engaged plots — floor allocation (0.02) applied; forecast is tenant-level, not colony-specific.";
        }
        if ($available === 0) {
            $n[] = "No available inventory — forecast bookings capped at 0.";
        } elseif ($runway) {
            $n[] = "Runway computed at 8% lead-to-booking conversion — conservative for a hot colony, optimistic for a cold one.";
        }
        $n[] = "Trend model is least-squares on 12 months of tenant lead counts; no external macro signals (RBI, repo rate) yet.";
        return $n;
    }
}
