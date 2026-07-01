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
            ['name' => 'Associate',       'min' => 0,          'color' => 'secondary'],
            ['name' => 'Senior Associate', 'min' => 25000,     'color' => 'orange'],
            ['name' => 'BDM',             'min' => 100000,    'color' => 'blue'],
            ['name' => 'Sr. BDM',         'min' => 300000,    'color' => 'success'],
            ['name' => 'Vice President',  'min' => 800000,    'color' => 'warning'],
            ['name' => 'President',       'min' => 2000000,   'color' => 'purple'],
            ['name' => 'Site Manager',    'min' => 5000000,   'color' => 'danger'],
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

    public function getTopAssociate(): array
    {
        try {
            $pdo = $this->resolvePdo();
            $stmt = $pdo->prepare("
                SELECT a.name, a.level, a.lifetime_sales, u.id
                FROM associates a
                JOIN users u ON u.id = a.user_id
                WHERE a.lifetime_sales IS NOT NULL AND a.lifetime_sales > 0
                ORDER BY a.lifetime_sales DESC
                LIMIT 1
            ");
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: ['name' => 'N/A', 'level' => 'N/A', 'metric' => 'N/A'];
        } catch (\Throwable $e) {
            return ['name' => 'N/A', 'level' => 'N/A', 'metric' => 'N/A'];
        }
    }

    public function getTopAgent(): array
    {
        try {
            $pdo = $this->resolvePdo();
            $stmt = $pdo->prepare("
                SELECT u.name, a.level, COALESCE(SUM(d.deal_value), 0) as total_deals
                FROM users u
                JOIN agents a ON a.user_id = u.id
                LEFT JOIN deals d ON d.assigned_to = u.id
                WHERE u.role = 'agent'
                GROUP BY u.id, u.name, a.level
                ORDER BY total_deals DESC
                LIMIT 1
            ");
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: ['name' => 'N/A', 'level' => 'N/A', 'metric' => 'N/A'];
        } catch (\Throwable $e) {
            return ['name' => 'N/A', 'level' => 'N/A', 'metric' => 'N/A'];
        }
    }

    public function getTopEmployee(): array
    {
        try {
            $pdo = $this->resolvePdo();
            $stmt = $pdo->prepare("
                SELECT u.name, COALESCE(SUM(pm.points), 0) as total_points
                FROM users u
                JOIN employees e ON e.user_id = u.id
                LEFT JOIN performance_metrics pm ON pm.employee_id = e.id
                WHERE u.role = 'employee'
                GROUP BY u.id, u.name
                ORDER BY total_points DESC
                LIMIT 1
            ");
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $row['level'] = $this->getEmployeeLevelName($row['total_points']);
            }
            return $row ?: ['name' => 'N/A', 'level' => 'N/A', 'metric' => 'N/A'];
        } catch (\Throwable $e) {
            return ['name' => 'N/A', 'level' => 'N/A', 'metric' => 'N/A'];
        }
    }

    private function getEmployeeLevelName(int $points): string
    {
        if ($points >= 1000) return 'Star';
        if ($points >= 600) return 'Lead';
        if ($points >= 300) return 'Senior';
        if ($points >= 100) return 'Junior';
        return 'Trainee';
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
            'next_target' => $remaining > 0 ? 'Earn ₹' . number_format($remaining) : 'Maxed out',
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
