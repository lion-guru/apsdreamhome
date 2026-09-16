<?php
/**
 * Admin Dashboard Customize - Drag-drop widget layout
 */
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
$widgets = $widgets ?? [];
$layouts = $layouts ?? [];
?>
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Customize Dashboard</h1>
        <div>
            <button class="btn btn-outline-secondary me-2" id="btn-reset-layout">
                <i class="fas fa-undo me-1"></i> Reset to Default
            </button>
            <button class="btn btn-primary" id="btn-save-layout">
                <i class="fas fa-save me-1"></i> Save Layout
            </button>
        </div>
    </div>

    <!-- Layout Selector -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Saved Layouts</h5>
        </div>
        <div class="card-body">
            <div class="row" id="layout-selector">
                <?php foreach ($layouts as $layout): ?>
                    <div class="col-md-3 mb-3">
                        <div class="card layout-card <?php echo $layout['is_default'] ? 'border-primary' : ''; ?>"
                             data-layout-id="<?php echo $layout['id']; ?>">
                            <div class="card-body text-center p-3">
                                <h6 class="mb-1"><?php echo htmlspecialchars($layout['layout_name']); ?></h6>
                                <?php if ($layout['is_default']): ?>
                                    <span class="badge bg-primary">Default</span>
                                <?php endif; ?>
                                <div class="mt-2">
                                    <button class="btn btn-sm btn-outline-primary btn-load-layout"
                                            data-id="<?php echo $layout['id']; ?>">
                                        <i class="fas fa-upload me-1"></i> Load
                                    </button>
                                    <?php if (!$layout['is_default']): ?>
                                        <button class="btn btn-sm btn-outline-danger btn-delete-layout ms-1"
                                                data-id="<?php echo $layout['id']; ?>">
                                            <i class="fas fa-trash me-1"></i> Delete
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="col-md-3 mb-3">
                    <button class="card btn btn-outline-secondary h-100 d-flex flex-column align-items-center justify-content-center"
                            id="btn-new-layout">
                        <i class="fas fa-plus fa-2x mb-2"></i>
                        <span>New Layout</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Available Widgets Palette -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Available Widgets</h5>
            <small class="text-muted">Drag to grid below</small>
        </div>
        <div class="card-body">
            <div class="row" id="widget-palette">
                <?php foreach ($widgets as $key => $widget): ?>
                    <div class="col-md-3 mb-3 widget-item" draggable="true"
                         data-widget-type="<?php echo $key; ?>">
                        <div class="card widget-card h-100">
                            <div class="card-body text-center p-3">
                                <i class="fas fa-<?php echo $widget['icon']; ?> fa-2x text-primary mb-2"></i>
                                <h6 class="mb-1"><?php echo $widget['name']; ?></h6>
                                <small class="text-muted">Drag to grid</small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Drop Grid -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Dashboard Grid (Drag widgets here)</h5>
        </div>
        <div class="card-body">
            <div class="grid-stack" id="dashboard-grid"
                 data-gs-animate="true"
                 data-gs-cell-height="80"
                 data-gs-min-row="10">
                <!-- Widgets will be placed here by GridStack -->
            </div>
        </div>
    </div>
</div>

<!-- Modal for New Layout Name -->
<div class="modal fade" id="modalNewLayout" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New Layout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
    <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Layout Name</label>
                <input type="text" class="form-control" id="newLayoutName" placeholder="My Custom Layout">
            </div>
    </div>
    <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" id="btn-create-layout">Create</button>
    </div>
    </div>
</div>

<style>
.widget-card { cursor: grab; transition: box-shadow 0.2s; }
.widget-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
.widget-card:active { cursor: grabbing; }
.grid-stack-item-content { background: #fff; border: 1px solid #dee2e6; border-radius: 0.375rem; }
.grid-stack-item-resizing .grid-stack-item-content { box-shadow: 0 0 15px rgba(0,0,0,0.2); }
.layout-card { cursor: pointer; transition: border-color 0.2s, box-shadow 0.2s; }
.layout-card:hover { border-color: #0d6efd; box-shadow: 0 2px 8px rgba(13,110,253,0.15); }
.widget-item { cursor: grab; }
.widget-item:active { cursor: grabbing; }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/gridstack/10.0.0/gridstack-all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js" defer></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const grid = GridStack.init({
        cellHeight: 80,
        minRow: 10,
        animate: true,
        acceptWidgets: '.widget-item',
        draggable: {
            handle: '.widget-card',
            scroll: true,
            appendTo: 'body'
        }
    });

    // Load existing widgets from default layout
    loadDefaultLayout();

    // Widget palette drag
    document.querySelectorAll('.widget-item').forEach(item => {
        item.addEventListener('dragstart', function(e) {
            e.dataTransfer.setData('widget-type', this.dataset.widgetType);
        });
    });

    // Save layout
    document.getElementById('btn-save-layout')?.addEventListener('click', function() {
        const layout = grid.save();
        const layoutName = prompt('Layout name:', 'My Layout') || 'Custom Layout';
        saveLayout(layoutName, layout);
    });

    // Reset to default
    document.getElementById('btn-reset-layout')?.addEventListener('click', function() {
        if (confirm('Reset to default layout?')) {
            loadDefaultLayout();
        }
    });

    // Load layout from saved
    document.querySelectorAll('.btn-load-layout').forEach(btn => {
        btn.addEventListener('click', function() {
            loadLayout(this.dataset.id);
        });
    });

    // Delete layout
    document.querySelectorAll('.btn-delete-layout').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Delete this layout?')) {
                deleteLayout(this.dataset.id);
            }
        });
    });

    // New layout modal
    document.getElementById('btn-new-layout')?.addEventListener('click', function() {
        new bootstrap.Modal(document.getElementById('modalNewLayout')).show();
    });

    document.getElementById('btn-create-layout')?.addEventListener('click', function() {
        const name = document.getElementById('newLayoutName').value.trim();
        if (name) {
            createLayout(name);
        }
    });

    function loadDefaultLayout() {
        const defaultLayout = [
            { type: 'stats_cards', x: 0, y: 0, w: 12, h: 2 },
            { type: 'recent_activity', x: 0, y: 2, w: 6, h: 4 },
            { type: 'quick_actions', x: 6, y: 2, w: 6, h: 4 }
        ];
        grid.load(defaultLayout);
        renderWidgets();
    }

    function renderWidgets() {
        grid.engine.nodes.forEach(node => {
            const el = node.el.querySelector('.grid-stack-item-content');
            if (el && !el.dataset.rendered) {
                el.dataset.rendered = 'true';
                const widgetType = node.content;
                el.innerHTML = '<?php echo $this->renderWidget("' + widgetType + '"); ?>';
                // Note: In production, use proper template rendering
            }
        });
    }

    function loadLayout(id) {
        fetch('<?php echo $base; ?>/admin/dashboard/layout/' + id, {
            method: 'GET',
            headers: { 'X-CSRF-Token': '<?php echo $_SESSION['csrf_token'] ?? ''; ?>' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.layout) {
                grid.load(data.layout.layout_config);
                renderWidgets();
            }
        });
    }

    function saveLayout(name, layout) {
        fetch('<?php echo $base; ?>/admin/dashboard/layout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': '<?php echo $_SESSION['csrf_token'] ?? ''; ?>'
            },
            body: JSON.stringify({ layout_name: name, layout_config: layout, is_default: true })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert('Layout saved!');
                location.reload();
            }
        });
    }

    function createLayout(name) {
        fetch('<?php echo $base; ?>/admin/dashboard/layout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': '<?php echo $_SESSION['csrf_token'] ?? ''; ?>'
            },
            body: JSON.stringify({ layout_name: name, layout_config: grid.save(), is_default: true })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalNewLayout')).hide();
                location.reload();
            }
        });
    }

    function deleteLayout(id) {
        fetch('<?php echo $base; ?>/admin/dashboard/layout/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-Token': '<?php echo $_SESSION['csrf_token'] ?? ''; ?>' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) location.reload();
        });
    }
</script>