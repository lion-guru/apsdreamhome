<?php
/**
 * KPI Chart Widget (placeholder - uses Chart.js)
 */
$chartData = $widgetData['chart_data'] ?? [
    'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
    'datasets' => [[
        'label' => 'Revenue',
        'data' => [120000, 190000, 150000, 210000, 250000, 220000],
        'borderColor' => '#0d6efd',
        'backgroundColor' => 'rgba(13, 110, 253, 0.1)',
    ]]
];
?>
<canvas id="kpiChart" height="200"></canvas>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('kpiChart');
    if (ctx && typeof Chart !== 'undefined') {
        new Chart(ctx, {
            type: 'line',
            data: <?php echo json_encode($chartData); ?>,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }
});
</script>