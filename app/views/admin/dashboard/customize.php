<?php
/**
 * Dashboard Widget Customization (Drag & Drop)
 * S354 - Suggestion #7
 * Uses HTML5 Drag and Drop API (no external library needed)
 */
$page_title = $page_title ?? 'Customize Dashboard';
$widgets = $widgets ?? [];
$savedLayout = $savedLayout ?? [];
?>

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
    $widgetDefs = [
        'stats_overview' => ['title' => 'Stats Overview', 'icon' => 'fa-chart-bar', 'color' => 'primary'],
        'recent_leads' => ['title' => 'Recent Leads', 'icon' => 'fa-users', 'color' => 'success'],
        'pending_tasks' => ['title' => 'Pending Tasks', 'icon' => 'fa-tasks', 'color' => 'warning'],
        'revenue_chart' => ['title' => 'Revenue Chart', 'icon' => 'fa-chart-line', 'color' => 'info'],
        'quick_actions' => ['title' => 'Quick Actions', 'icon' => 'fa-bolt', 'color' => 'danger'],
        'notifications' => ['title' => 'Notifications', 'icon' => 'fa-bell', 'color' => 'secondary'],
        'calendar' => ['title' => 'Calendar', 'icon' => 'fa-calendar', 'color' => 'primary'],
        'team_performance' => ['title' => 'Team Performance', 'icon' => 'fa-trophy', 'color' => 'success'],
    ];
    
    $layout = !empty($savedLayout) ? $savedLayout : array_keys($widgetDefs);
    foreach ($layout as $widgetId):
        if (!isset($widgetDefs[$widgetId])) continue;
        $w = $widgetDefs[$widgetId];
    ?>
    <div class="col-md-6 col-lg-4 mb-3 widget-item" draggable="true" data-widget="<?= $widgetId ?>">
        <div class="card h-100 widget-card" style="cursor: move;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2  class="fs-6 fw-semibold mb-0"><i class="fas <?= $w['icon'] ?> me-2 text-<?= $w['color'] ?>"></i><?= $w['title'] ?></h2>
                <i class="fas fa-grip-vertical text-muted"></i>
            </div>
            <div class="card-body">
                <div class="text-center text-muted py-3">
                    <i class="fas <?= $w['icon'] ?> fa-2x mb-2 opacity-50"></i>
                    <p class="small mb-0"><?= $w['title'] ?> widget content</p>
                </div>
            </div>
        </div>
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

function getLayout() {
    return Array.from(document.querySelectorAll('.widget-item')).map(el => el.dataset.widget);
}

function saveLayout() {
    const layout = getLayout();
    fetch('/admin/dashboard/customize/save', {
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
    fetch('/admin/dashboard/customize/reset', {
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
