<?php
/**
 * Recent Activity Widget Partial
 * Expected $widget config: title, data_source, limit
 */
$config = $widget['config'] ?? [];
$title = $config['title'] ?? '';
$dataSource = $config['data_source'] ?? '';
$limit = $config['limit'] ?? 10;
$widgetId = $widget['id'] ?? 'activity_' . uniqid();
?>

<div class="grid-stack-item" data-gs-id="<?= htmlspecialchars($widgetId) ?>" data-gs-x="<?= (int)($widget['x'] ?? 0) ?>" data-gs-y="<?= (int)($widget['y'] ?? 0) ?>" data-gs-w="<?= (int)($widget['w'] ?? 8) ?>" data-gs-h="<?= (int)($widget['h'] ?? 8) ?>">
    <div class="widget-card activity-card">
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
            <div class="activity-list" id="activity_<?= htmlspecialchars($widgetId) ?>">
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-spinner fa-spin fa-2x mb-2"></i>
                    <p>Loading activities...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('activity_<?= htmlspecialchars($widgetId) ?>');
    if (!container) return;
    
    const dataSource = '<?= htmlspecialchars($dataSource) ?>';
    const limit = <?= (int)$limit ?>;
    
    fetch('<?= BASE_URL ?>/admin/dashboard/widget-data/recent_activity?data_source=' + encodeURIComponent(dataSource) + '&limit=' + limit)
        .then(res => res.json())
        .then(res => {
            if (!res.success || !res.data || !res.data.activities) {
                container.innerHTML = '<div class="text-center py-4 text-muted"><i class="fas fa-inbox fa-2x mb-2"></i><p>No recent activities</p></div>';
                return;
            }
            
            const activities = res.data.activities;
            if (activities.length === 0) {
                container.innerHTML = '<div class="text-center py-4 text-muted"><i class="fas fa-inbox fa-2x mb-2"></i><p>No recent activities</p></div>';
                return;
            }
            
            container.innerHTML = activities.map(activity => `
                <div class="activity-item d-flex align-items-start gap-3 mb-3 pb-3 border-bottom">
                    <div class="flex-shrink-0">
                        <div class="rounded-circle bg-info bg-opacity-10 p-2">
                            <i class="fas fa-circle fa-sm text-info"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-medium small">${escapeHtml(activity.title ?? '')}</div>
                        <div class="text-muted small">${escapeHtml(activity.description ?? activity.action ?? '')}</div>
                    </div>
                    <div class="text-muted small">${activity.date ? new Date(activity.date).toLocaleDateString('en-US', {month: 'short', day: 'numeric'}) : ''}</div>
                </div>
            `).join('');
        })
        .catch(err => {
            console.error('Activity load error:', err);
            container.innerHTML = '<div class="text-center py-4 text-muted"><i class="fas fa-exclamation-triangle fa-2x mb-2 text-warning"></i><p>Failed to load activities</p></div>';
        });
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>