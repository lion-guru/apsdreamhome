<?php
namespace App\Services;

use PDO;

/**
 * GamificationService — computes level/rank/progress for any user role.
 * Returns arrays shaped exactly for `app/views/components/gamification_widget.php`.
 */
class GamificationService
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? $this->resolvePdo();
    }

    public function forCustomer(int $userId): array
    {
        $stats = (new InvestmentService($this->pdo))->getStats($userId);
        $color = match($stats['level']) { 'Diamond' => 'indigo', 'Platinum' => 'purple', 'Gold' => 'orange', 'Silver' => 'secondary', default => 'primary' };
        $remaining = max(0, (float)$stats['next_threshold'] - (float)$stats['total_invested']);
        return [
            'title' => 'Investor Level',
            'icon' => 'fa-trophy',
            'level' => $stats['level'],
            'level_color' => $color,
            'metric' => 'Total Invested: ₹' . number_format((float)$stats['total_invested']),
            'progress_pct' => (float)$stats['progress_pct'],
            'next_label' => $stats['next_level'],
            'next_target' => 'Invest ₹' . number_format($remaining),
            'cta_url' => '/user/investment-plans',
            'cta_text' => 'Upgrade',
            'gradient' => 'linear-gradient(135deg, #fff 0%, #ede9fe 100%)',
        ];
    }

    public function forAssociate(int $userId, int $associateId): array
    {
        $teamSales = 0.0;
        try {
            $stmt = $this->pdo->prepare("SELECT COALESCE(SUM(amount),0) s FROM mlm_commission_ledger WHERE beneficiary_user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)");
            $stmt->execute([$userId]);
            $teamSales = (float)$stmt->fetchColumn();
        } catch (\Throwable $e) {
            try {
                $stmt = $this->pdo->prepare("SELECT COALESCE(SUM(amount),0) s FROM mlm_commissions WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)");
                $stmt->execute([$userId]);
                $teamSales = (float)$stmt->fetchColumn();
            } catch (\Throwable $e2) {}
        }

        $networkSize = 0;
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM mlm_profiles WHERE sponsor_user_id = ?");
            $stmt->execute([$userId]);
            $networkSize = (int)$stmt->fetchColumn();
        } catch (\Throwable $e) {}

        $thresholds = [
            ['name' => 'Associate', 'min' => 0,         'color' => 'secondary'],
            ['name' => 'Bronze',    'min' => 50000,     'color' => 'orange'],
            ['name' => 'Silver',    'min' => 200000,    'color' => 'secondary'],
            ['name' => 'Gold',      'min' => 500000,    'color' => 'orange'],
            ['name' => 'Platinum',  'min' => 1000000,   'color' => 'purple'],
            ['name' => 'Diamond',   'min' => 2500000,   'color' => 'indigo'],
        ];
        return $this->buildTieredWidget('MLM Rank', 'fa-medal', $teamSales, $thresholds, 'Team Sales (12 mo): ₹' . number_format($teamSales), '/associate/commissions', 'View Earnings', 'linear-gradient(135deg, #fff 0%, #dbeafe 100%)');
    }

    public function forAgent(int $userId, int $agentId): array
    {
        $deals = 0; $revenue = 0.0;
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) c, COALESCE(SUM(amount),0) s FROM deals WHERE agent_id = ? AND status = 'won' AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)");
            $stmt->execute([$agentId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) { $deals = (int)$row['c']; $revenue = (float)$row['s']; }
        } catch (\Throwable $e) {}

        $thresholds = [
            ['name' => 'Rookie',    'min' => 0,       'color' => 'secondary'],
            ['name' => 'Closer',    'min' => 500000,  'color' => 'blue'],
            ['name' => 'Pro',       'min' => 2000000, 'color' => 'primary'],
            ['name' => 'Elite',     'min' => 5000000, 'color' => 'orange'],
            ['name' => 'Champion',  'min' => 10000000,'color' => 'purple'],
        ];
        return $this->buildTieredWidget('Agent Rank', 'fa-award', $revenue, $thresholds, 'Deals Won: ' . $deals . ' | Revenue (12 mo): ₹' . number_format($revenue), '/agent/deals', 'View Pipeline', 'linear-gradient(135deg, #fff 0%, #d1fae5 100%)');
    }

    public function forEmployee(int $employeeId): array
    {
        $score = 0;
        try {
            $stmt = $this->pdo->prepare("SELECT COALESCE(SUM(points),0) FROM performance_metrics WHERE employee_id = ? AND metric_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)");
            $stmt->execute([$employeeId]);
            $score = (int)$stmt->fetchColumn();
        } catch (\Throwable $e) {
            $score = 0;
        }

        $thresholds = [
            ['name' => 'Trainee',   'min' => 0,    'color' => 'secondary'],
            ['name' => 'Junior',    'min' => 100,  'color' => 'blue'],
            ['name' => 'Senior',    'min' => 300,  'color' => 'primary'],
            ['name' => 'Lead',      'min' => 600,  'color' => 'orange'],
            ['name' => 'Star',      'min' => 1000, 'color' => 'purple'],
        ];
        return $this->buildTieredWidget('Performance Tier', 'fa-star', $score, $thresholds, 'Performance Score (12 mo): ' . number_format($score) . ' pts', '/employee/performance', 'View Details', 'linear-gradient(135deg, #fff 0%, #fed7aa 100%)');
    }

    private function buildTieredWidget(string $title, string $icon, float $value, array $thresholds, string $metric, string $ctaUrl, string $ctaText, string $gradient): array
    {
        $current = $thresholds[0];
        $next = $thresholds[count($thresholds) - 1];
        foreach ($thresholds as $i => $t) {
            if ($value >= $t['min']) { $current = $t; $next = $thresholds[$i + 1] ?? $t; }
        }
        $span = max(1, $next['min'] - $current['min']);
        $pct = $next === $current ? 100.0 : min(100.0, (($value - $current['min']) / $span) * 100.0);
        $remaining = max(0, $next['min'] - $value);
        return [
            'title' => $title,
            'icon' => $icon,
            'level' => $current['name'],
            'level_color' => $current['color'],
            'metric' => $metric,
            'progress_pct' => $pct,
            'next_label' => $next['name'],
            'next_target' => $remaining > 0 ? 'Earn ' . number_format($remaining) . ' more' : 'Maxed out',
            'cta_url' => $ctaUrl,
            'cta_text' => $ctaText,
            'gradient' => $gradient,
        ];
    }

    private function resolvePdo(): PDO
    {
        $cfg = [
            'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
            'port' => $_ENV['DB_PORT'] ?? '3307',
            'dbname' => $_ENV['DB_DATABASE'] ?? 'apsdreamhome',
            'user' => $_ENV['DB_USERNAME'] ?? 'root',
            'pass' => $_ENV['DB_PASSWORD'] ?? '',
        ];
        return new PDO("mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['dbname']}", $cfg['user'], $cfg['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }
}
