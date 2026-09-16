<?php
/**
 * Chart Widget Partial
 * Expected $widget config: title, chart_type, data_source, height
 */
$config = $widget['config'] ?? [];
$title = $config['title'] ?? '';
$chartType = $config['chart_type'] ?? 'line';
$dataSource = $config['data_source'] ?? '';
$height = $config['height'] ?? 300;
$widgetId = $widget['id'] ?? 'chart_' . uniqid();
?>

<div class="grid-stack-item" data-gs-id="<?= htmlspecialchars($widgetId) ?>" data-gs-x="<?= (int)($widget['x'] ?? 0) ?>" data-gs-y="<?= (int)($widget['y'] ?? 0) ?>" data-gs-w="<?= (int)($widget['w'] ?? 6) ?>" data-gs-h="<?= (int)($widget['h'] ?? 8) ?>">
    <div class="widget-card chart-card">
        <div class="widget-header">
            <h6 class="widget-title"><?= htmlspecialchars($title) ?></h6>
            <div class="widget-actions">
                <button type="button" class="btn btn-sm btn-outline-secondary widget-config" data-widget-id="<?= htmlspecialchars($widgetId) ?>" title="Configure">
                    <i class="fas fa-cog"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger widget-remove" data-widget-id="<?= htmlspecialchars($widgetId) ?>" title="Remove">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="widget-body">
            <canvas id="chart_<?= htmlspecialchars($widgetId) ?>" height="<?= (int)$height ?>"></canvas>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('chart_<?= htmlspecialchars($widgetId) ?>');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    const dataSource = '<?= htmlspecialchars($dataSource) ?>';
    
    // Fetch chart data from API
    fetch('<?= BASE_URL ?>/admin/dashboard/widget-data/chart_widget?data_source=' + encodeURIComponent(dataSource) + '&config=' + encodeURIComponent(JSON.stringify(<?= json_encode($config) ?>)))
        .then(res => res.json())
        .then(res => {
            if (!res.success || !res.data) return;
            
            const chartData = res.data;
            new Chart(ctx, {
                type: '<?= htmlspecialchars($chartType) ?>',
                data: {
                    labels: chartData.labels || [],
                    datasets: [{
                        label: '<?= htmlspecialchars($title) ?>',
                        data: chartData.data || [],
                        backgroundColor: 'rgba(59, 130, 246, 0.2)',
                        borderColor: 'rgb(59, 130, 246)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { 
                            beginAtZero: true, 
                            ticks: { 
                                callback: function(v) { 
                                    return v >= 1000 ? (v/1000).toFixed(1)+'k' : v; 
                                } 
                            } 
                        }
                    }
                }
            });
        })
        .catch(err => console.error('Chart load error:', err));
});
</script>