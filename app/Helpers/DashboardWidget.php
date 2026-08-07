<?php
/**
 * Dashboard Widget Helper
 * 
 * Creates dashboard stats cards and charts
 * Usage: DashboardWidget::statCard('Title', 'value', 'icon', 'color')
 */

namespace App\Helpers;

class DashboardWidget
{
    /**
     * Render a stats card
     */
    public static function statCard(string $title, $value, string $icon = 'fa-chart-line', string $color = 'primary'): string
    {
        return '<div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-' . $color . ' h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase mb-1">' . htmlspecialchars($title) . '</h6>
                            <h3 class="mb-0">' . htmlspecialchars((string) $value) . '</h3>
                        </div>
                        <div class="icon-circle bg-' . $color . ' text-white">
                            <i class="fas ' . htmlspecialchars($icon) . '"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>';
    }

    /**
     * Render a progress card
     */
    public static function progressCard(string $title, $current, $target, string $color = 'success'): string
    {
        $percentage = $target > 0 ? round(($current / $target) * 100) : 0;
        return '<div class="col-md-4 col-sm-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase">' . htmlspecialchars($title) . '</h6>
                    <div class="d-flex justify-content-between">
                        <h4>' . htmlspecialchars((string) $current) . '</h4>
                        <span class="text-muted">/ ' . htmlspecialchars((string) $target) . '</span>
                    </div>
                    <div class="progress mt-2" style="height: 8px;">
                        <div class="progress-bar bg-' . $color . '" style="width: ' . min(100, $percentage) . '%"></div>
                    </div>
                    <small class="text-muted">' . $percentage . '% complete</small>
                </div>
            </div>
        </div>';
    }

    /**
     * Render a quick action card
     */
    public static function quickAction(string $title, string $url, string $icon, string $color = 'primary'): string
    {
        return '<div class="col-md-3 col-sm-6 mb-3">
            <a href="' . htmlspecialchars($url) . '" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="fas ' . htmlspecialchars($icon) . ' fa-2x text-' . $color . ' mb-2"></i>
                    <h6 class="mb-0">' . htmlspecialchars($title) . '</h6>
                </div>
            </a>
        </div>';
    }

    /**
     * Render a chart container
     */
    public static function chartCard(string $title, string $chartId, int $height = 300): string
    {
        return '<div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">' . htmlspecialchars($title) . '</h5>
            </div>
            <div class="card-body">
                <canvas id="' . htmlspecialchars($chartId) . '" height="' . $height . '"></canvas>
            </div>
        </div>';
    }

    /**
     * Render recent activity list
     */
    public static function activityList(array $activities): string
    {
        $html = '<div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Recent Activity</h5></div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">';
        
        foreach ($activities as $activity) {
            $html .= '<li class="list-group-item d-flex justify-content-between align-items-center">
                <span>' . htmlspecialchars($activity['text'] ?? '') . '</span>
                <small class="text-muted">' . htmlspecialchars($activity['time'] ?? '') . '</small>
            </li>';
        }
        
        $html .= '</ul></div></div>';
        return $html;
    }

    /**
     * Render a row of stat cards
     */
    public static function statRow(array $cards): string
    {
        $html = '<div class="row mb-4">';
        foreach ($cards as $card) {
            $html .= self::statCard(
                $card['title'] ?? '',
                $card['value'] ?? 0,
                $card['icon'] ?? 'fa-chart-line',
                $card['color'] ?? 'primary'
            );
        }
        $html .= '</div>';
        return $html;
    }
}
