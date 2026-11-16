<?php
/**
 * Dashboard Widget Customization (Drag & Drop)
 * S354 - Suggestion #7
 * Uses HTML5 Drag and Drop API (no external library needed)
 */
$page_title = $page_title ?? 'Customize Dashboard';
$widgets = $widgets ?? [];
$savedLayout = $savedLayout ?? [];
$kpis = $kpis ?? [];
?>

<script src="<?= BASE_URL ?>/assets/js/vendor/chart.umd.js"></script>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.widget-item { list-style: none; }
.widget-item .widget-card,
.widget-item .grid-stack-item > .widget-card {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 1px 3px rgba(2, 6, 23, 0.06);
    transition: box-shadow 0.15s ease, border-color 0.15s ease;
    overflow: hidden;
}
.widget-item .widget-card:hover { border-color: #cbd5e1; box-shadow: 0 4px 12px rgba(2,6,23,0.08); }
.widget-item .widget-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 0.6rem 0.9rem; border-bottom: 1px solid #e2e8f0;
}
.widget-item .widget-title { margin: 0; font-size: 0.8rem; font-weight: 700; color: #0f172a; }
.widget-item .widget-actions { display: flex; gap: 0.25rem; }
.widget-item .widget-config { display: none; } /* no config UI yet - hide dead cog */
.widget-item .widget-body { padding: 0.75rem 0.9rem; }
.widget-item .kpi-content { display: flex; align-items: center; gap: 0.75rem; }
.widget-item .kpi-icon { width: 56px; height: 56px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex: 0 0 auto; }
.widget-item .kpi-value { font-size: 1.75rem; font-weight: 800; color: #0f172a; line-height: 1.1; }
.widget-item .kpi-unit { font-size: 0.7rem; color: #64748b; margin-top: 0.2rem; text-transform: uppercase; letter-spacing: 0.05em; }
.widget-item .kpi-progress { margin-top: 0.7rem; }
.widget-item .kpi-target { margin-top: 0.25rem; }
.widget-item canvas { max-width: 100%; }
.widget-item table { width: 100%; font-size: 0.8rem; border-collapse: collapse; }
.widget-item th { text-align: left; padding: 0.35rem 0.5rem; color: #64748b; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.04em; border-bottom: 2px solid #e2e8f0; }
.widget-item td { padding: 0.35rem 0.5rem; border-bottom: 1px solid #f1f5f9; color: #1e293b; }
.widget-item .empty-hint { color: #94a3b8; font-size: 0.8rem; padding: 1.25rem 0; text-align: center; }
</style>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-th-large me-2 text-primary"></i>Customize Dashboard</h4>
            <div>
                <button class="btn btn-outline-secondary me-2" onclick="resetLayout()">
                    <i class="fas fa-undo me-1"></i>Reset
                </button>
                <button class="btn btn-primary" onclick="saveLayout()">
                    <i class="fas fa-save me-1"></i>Save Layout
                </button>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i>
    Drag and drop widgets to rearrange your dashboard. Click "Save Layout" to persist your arrangement.
</div>

<div class="row" id="widgetContainer">
    <?php
    $liveWidgetDefs = [
        'stats_overview' => ['title' => 'Stats Overview', 'icon' => 'fa-chart-bar', 'color' => 'primary', 'type' => 'kpi'],
        'recent_leads' => ['title' => 'Recent Leads', 'icon' => 'fa-users', 'color' => 'success', 'type' => 'table'],
        'pending_tasks' => ['title' => 'Pending Tasks', 'icon' => 'fa-tasks', 'color' => 'warning', 'type' => 'kpi'],
        'revenue_chart' => ['title' => 'Revenue Chart', 'icon' => 'fa-chart-line', 'color' => 'info', 'type' => 'chart'],
        'leads_chart' => ['title' => 'Leads Chart', 'icon' => 'fa-chart-area', 'color' => 'primary', 'type' => 'chart'],
        'pending_followups' => ['title' => 'Pending Follow-ups', 'icon' => 'fa-phone-alt', 'color' => 'warning', 'type' => 'table'],
        'quick_actions' => ['title' => 'Quick Actions', 'icon' => 'fa-bolt', 'color' => 'danger', 'type' => 'static'],
        'notifications' => ['title' => 'Notifications', 'icon' => 'fa-bell', 'color' => 'secondary', 'type' => 'kpi'],
        'calendar' => ['title' => 'Calendar', 'icon' => 'fa-calendar', 'color' => 'primary', 'type' => 'empty'],
        'team_performance' => ['title' => 'Team Performance', 'icon' => 'fa-trophy', 'color' => 'success', 'type' => 'empty'],
    ];

    $layout = !empty($savedLayout) ? $savedLayout : ['stats_overview', 'revenue_chart', 'recent_leads', 'pending_tasks', 'notifications', 'pending_followups'];
    foreach ($layout as $widgetId):
        if (!isset($liveWidgetDefs[$widgetId])) continue;
        $w = $liveWidgetDefs[$widgetId];
    ?>
    <div class="col-md-6 col-lg-4 mb-3 widget-item" draggable="true" data-widget="<?= $widgetId ?>">
        <?php if ($w['type'] === 'kpi' && $widgetId === 'stats_overview'): ?>
                    <?php $widget = ['id' => 'stats_overview', 'x' => 0, 'y' => 0, 'w' => 4, 'h' => 6, 'config' => [
                        'title' => 'Leads This Month',
                        'value' => $kpis['leads_month'] ?? 0,
                        'target' => 0,
                        'unit' => 'leads',
                        'icon' => 'fas fa-bullseye',
                        'color' => '#10b981',
                        'format' => 'number',
                    ]]; ?>
                    <?php include __DIR__ . '/widgets/kpi_widget.php'; ?>
                <?php elseif ($w['type'] === 'kpi' && $widgetId === 'pending_tasks'): ?>
                    <?php $widget = ['id' => 'pending_tasks', 'x' => 4, 'y' => 0, 'w' => 4, 'h' => 6, 'config' => [
                        'title' => 'Pending Follow-ups',
                        'value' => $kpis['pending_followups'] ?? 0,
                        'target' => 0,
                        'unit' => 'follow-ups',
                        'icon' => 'fas fa-tasks',
                        'color' => '#f59e0b',
                        'format' => 'number',
                    ]]; ?>
                    <?php include __DIR__ . '/widgets/kpi_widget.php'; ?>
                <?php elseif ($w['type'] === 'kpi' && $widgetId === 'notifications'): ?>
                    <?php $widget = ['id' => 'notifications', 'x' => 8, 'y' => 0, 'w' => 4, 'h' => 6, 'config' => [
                        'title' => 'Unread Notifications',
                        'value' => $kpis['unread_notifications'] ?? 0,
                        'target' => 0,
                        'unit' => 'unread',
                        'icon' => 'fas fa-bell',
                        'color' => '#8b5cf6',
                        'format' => 'number',
                    ]]; ?>
                    <?php include __DIR__ . '/widgets/kpi_widget.php'; ?>
                <?php elseif ($w['type'] === 'chart'): ?>
                    <div style="height: 220px;">
                        <?php $widget = ['id' => $widgetId, 'x' => 0, 'y' => 0, 'w' => 6, 'h' => 8, 'config' => [
                            'title' => $widgetId === 'revenue_chart' ? 'Revenue (6 months)' : 'Leads (6 months)',
                            'chart_type' => $widgetId === 'revenue_chart' ? 'bar' : 'line',
                            'data_source' => $widgetId === 'revenue_chart' ? 'revenue_monthly_6m' : 'leads_monthly_6m',
                            'height' => 220,
                        ]]; ?>
                        <?php include __DIR__ . '/widgets/chart_widget.php'; ?>
                    </div>
                <?php elseif ($w['type'] === 'table' && $widgetId === 'recent_leads'): ?>
                    <div class="table-responsive">
                        <?php $widget = ['id' => 'recent_leads', 'x' => 0, 'y' => 0, 'w' => 12, 'h' => 10, 'config' => [
                            'title' => 'Recent Leads',
                            'columns' => [
                                ['key' => 'name', 'label' => 'Lead'],
                                ['key' => 'phone', 'label' => 'Phone'],
                                ['key' => 'status', 'label' => 'Status'],
                                ['key' => 'created_at', 'label' => 'Created', 'format' => 'date'],
                            ],
                            'data_source' => 'recent_leads',
                            'page_size' => 8,
                        ]]; ?>
                        <?php include __DIR__ . '/widgets/table_widget.php'; ?>
                    </div>
                <?php elseif ($w['type'] === 'table' && $widgetId === 'pending_followups'): ?>
                    <div class="table-responsive">
                        <?php $widget = ['id' => 'pending_followups', 'x' => 0, 'y' => 0, 'w' => 12, 'h' => 10, 'config' => [
                            'title' => 'Pending Follow-ups',
                            'columns' => [
                                ['key' => 'name', 'label' => 'Lead'],
                                ['key' => 'phone', 'label' => 'Phone'],
                                ['key' => 'status', 'label' => 'Status'],
                                ['key' => 'next_activity_date', 'label' => 'Next Follow-up', 'format' => 'date'],
                            ],
                            'data_source' => 'pending_followups',
                            'page_size' => 8,
                        ]]; ?>
                        <?php include __DIR__ . '/widgets/table_widget.php'; ?>
                    </div>
                <?php elseif ($w['type'] === 'static' && $widgetId === 'quick_actions'): ?>
                    <div class="widget-card">
                        <div class="widget-header">
                            <h6 class="widget-title"><i class="fas <?= $w['icon'] ?> me-2 text-<?= $w['color'] ?>"></i><?= $w['title'] ?></h6>
                        </div>
                        <div class="widget-body">
                            <div class="d-grid gap-2">
                                <a href="<?= BASE_URL ?>/admin/leads" class="btn btn-outline-success btn-sm"><i class="fas fa-user-plus me-1"></i> New Lead</a>
                                <a href="<?= BASE_URL ?>/admin/sales/bookings/create" class="btn btn-outline-primary btn-sm"><i class="fas fa-file-signature me-1"></i> New Booking</a>
                                <a href="<?= BASE_URL ?>/admin/payments" class="btn btn-outline-info btn-sm"><i class="fas fa-rupee-sign me-1"></i> Record Payment</a>
                                <a href="<?= BASE_URL ?>/admin/properties" class="btn btn-outline-secondary btn-sm"><i class="fas fa-home me-1"></i> Properties</a>
                                <a href="<?= BASE_URL ?>/admin/colonies" class="btn btn-outline-dark btn-sm"><i class="fas fa-map-marked-alt me-1"></i> Colonies</a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="widget-card">
                        <div class="widget-header">
                            <h6 class="widget-title"><i class="fas <?= $w['icon'] ?> me-2 text-<?= $w['color'] ?>"></i><?= $w['title'] ?></h6>
                        </div>
                        <div class="widget-body">
                            <div class="empty-hint">
                                <i class="fas <?= $w['icon'] ?> fa-2x mb-2 opacity-50"></i>
                                <p class="small mb-0">Not enabled yet</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>

<script>
let draggedItem = null;

document.querySelectorAll('.widget-item').forEach(item => {
    item.addEventListener('dragstart', function(e) {
        draggedItem = this;
        this.style.opacity = '0.5';
        e.dataTransfer.effectAllowed = 'move';
    });
    
    item.addEventListener('dragend', function() {
        this.style.opacity = '1';
        draggedItem = null;
    });
    
    item.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
    });
    
    item.addEventListener('drop', function(e) {
        e.preventDefault();
        if (draggedItem && draggedItem !== this) {
            const container = document.getElementById('widgetContainer');
            const allItems = Array.from(container.children);
            const draggedIndex = allItems.indexOf(draggedItem);
            const targetIndex = allItems.indexOf(this);
            
            if (draggedIndex < targetIndex) {
                this.parentNode.insertBefore(draggedItem, this.nextSibling);
            } else {
                this.parentNode.insertBefore(draggedItem, this);
            }
        }
    });
});

// Remove buttons inside partials drop the widget from the drag-drop grid
document.addEventListener('click', function (e) {
    const btn = e.target.closest('.widget-remove');
    if (!btn) return;
    const item = btn.closest('.widget-item');
    if (item) item.remove();
});

function getLayout() {
    return Array.from(document.querySelectorAll('.widget-item')).map(el => el.dataset.widget);
}

function saveLayout() {
    const layout = getLayout();
    fetch('<?= BASE_URL ?>/admin/dashboard/customize/save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?? '' ?>' },
        body: JSON.stringify({ layout: layout })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('Layout saved successfully!');
        } else {
            alert('Failed to save: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(() => alert('Failed to save layout'));
}

function resetLayout() {
    if (!confirm('Reset to default layout?')) return;
    fetch('<?= BASE_URL ?>/admin/dashboard/customize/reset', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?? '' ?>' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
        else alert('Failed to reset');
    })
    .catch(() => alert('Failed to reset layout'));
}
</script>
