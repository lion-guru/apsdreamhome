<?php
/**
 * Table Widget Partial
 * Expected $widget config: title, columns, data_source, page_size
 */
$config = $widget['config'] ?? [];
$title = $config['title'] ?? '';
$columns = $config['columns'] ?? [];
$dataSource = $config['data_source'] ?? '';
$pageSize = $config['page_size'] ?? 10;
$widgetId = $widget['id'] ?? 'table_' . uniqid();
?>

<div class="grid-stack-item" data-gs-id="<?= htmlspecialchars($widgetId) ?>" data-gs-x="<?= (int)($widget['x'] ?? 0) ?>" data-gs-y="<?= (int)($widget['y'] ?? 0) ?>" data-gs-w="<?= (int)($widget['w'] ?? 12) ?>" data-gs-h="<?= (int)($widget['h'] ?? 10) ?>">
    <div class="widget-card table-card">
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
        <div class="widget-body table-responsive">
            <table class="table table-hover table-sm widget-table" id="table_<?= htmlspecialchars($widgetId) ?>">
                <thead>
                    <tr>
                        <?php foreach ($columns as $col): ?>
                        <th><?= htmlspecialchars($col['label'] ?? $col['key'] ?? '') ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data loaded via JS -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tableId = 'table_<?= htmlspecialchars($widgetId) ?>';
    const table = document.getElementById(tableId);
    if (!table) return;
    
    const columns = <?= json_encode($columns) ?>;
    const dataSource = '<?= htmlspecialchars($dataSource) ?>';
    
    // Fetch table data
    fetch('<?= BASE_URL ?>/admin/dashboard/widget-data/table_widget?data_source=' + encodeURIComponent(dataSource) + '&limit=' + <?= (int)$pageSize ?>)
        .then(res => res.json())
        .then(res => {
            if (!res.success || !res.data || !res.data.records) return;
            
            const tbody = table.querySelector('tbody');
            tbody.innerHTML = '';
            
            res.data.records.forEach(record => {
                const tr = document.createElement('tr');
                columns.forEach(col => {
                    const td = document.createElement('td');
                    const key = col['key'] ?? '';
                    td.textContent = record[key] ?? '';
                    if (col['format'] === 'currency') {
                        td.textContent = '₹' + Number(record[key] ?? 0).toLocaleString('en-IN');
                    } else if (col['format'] === 'date') {
                        td.textContent = record[key] ? new Date(record[key]).toLocaleDateString() : '';
                    }
                    tr.appendChild(td);
                });
                tbody.appendChild(tr);
            });
        })
        .catch(err => console.error('Table load error:', err));
});
</script>